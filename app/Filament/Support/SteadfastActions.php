<?php

namespace App\Filament\Support;

use App\Exceptions\CourierException;
use App\Models\Order;
use App\Models\Store;
use App\Services\SteadfastClient;
use App\Support\BookSteadfast;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Client\ConnectionException;

class SteadfastActions
{
    /** Only owners and managers may create parcels or request returns. */
    private static function canManage(): bool
    {
        $store = Filament::getTenant();

        return $store instanceof Store
            && auth()->user()?->hasRoleIn($store, [Store::ROLE_OWNER, Store::ROLE_MANAGER]);
    }

    private static function refreshPage(mixed $livewire): void
    {
        if (is_object($livewire) && method_exists($livewire, 'refreshFormData')) {
            $livewire->refreshFormData(['status', 'consignment_id', 'tracking_code', 'courier_status', 'courier_message', 'courier_booked_at']);
        }
    }

    public static function book(): Action
    {
        return Action::make('sendToSteadfast')
            ->label('Send to Steadfast')
            ->icon('heroicon-o-truck')
            ->requiresConfirmation()
            ->modalHeading('Send this order to Steadfast?')
            ->modalDescription('This creates a real parcel with Steadfast Courier. The customer will pay the full total in cash on delivery.')
            ->visible(fn (Order $record) => self::canManage()
                && $record->status === 'confirmed'
                && blank($record->consignment_id))
            ->action(function (Order $record, $livewire) {
                try {
                    app(BookSteadfast::class)->handle($record);

                    Notification::make()->success()
                        ->title('Sent to Steadfast')
                        ->body('Tracking code: ' . ($record->refresh()->tracking_code ?: 'will appear shortly'))
                        ->send();

                    self::refreshPage($livewire);
                } catch (CourierException $e) {
                    Notification::make()->danger()->title('Could not send this order')->body($e->getMessage())->send();
                } catch (ConnectionException) {
                    Notification::make()->danger()->title('Could not reach Steadfast')->body('Nothing was sent. Please try again in a moment.')->send();
                }
            });
    }

    public static function bulk(): BulkAction
    {
        return BulkAction::make('bulkSendToSteadfast')
            ->label('Send selected to Steadfast')
            ->icon('heroicon-o-truck')
            ->requiresConfirmation()
            ->modalHeading('Send the selected orders to Steadfast?')
            ->modalDescription('Only confirmed orders that have not been sent yet are sent, up to 25 at a time. Each one creates a real parcel.')
            ->visible(fn () => self::canManage())
            ->deselectRecordsAfterCompletion()
            ->action(function (Collection $records) {
                $result = app(BookSteadfast::class)->handleMany($records->take(25));

                $body = $result['booked'] . ' sent.';

                if ($result['skipped']) {
                    $body .= ' ' . $result['skipped'] . ' skipped (not confirmed, or already sent).';
                }

                if ($result['failed']) {
                    $body .= ' ' . count($result['failed']) . ' failed: ' . implode('; ', array_slice($result['failed'], 0, 3));
                }

                $notification = Notification::make()->title('Steadfast booking finished')->body($body);

                ($result['failed'] ? $notification->warning() : $notification->success())->send();
            });
    }

    public static function returnRequest(): Action
    {
        return Action::make('requestReturn')
            ->label('Request return')
            ->icon('heroicon-o-arrow-uturn-left')
            ->color('warning')
            ->modalHeading('Ask Steadfast to return this parcel?')
            ->modalDescription('Use this when the customer no longer wants the parcel and it has not been delivered yet.')
            ->schema([
                Textarea::make('reason')->label('Reason (optional)')->rows(2)->maxLength(250),
            ])
            ->visible(fn (Order $record) => self::canManage()
                && filled($record->consignment_id)
                && blank($record->return_requested_at)
                && ! in_array($record->courier_status, ['delivered', 'partial_delivered', 'cancelled'], true))
            ->action(function (array $data, Order $record) {
                try {
                    (new SteadfastClient($record->store))->createReturnRequest($record->consignment_id, $data['reason'] ?? null);

                    $record->forceFill(['return_requested_at' => now()])->save();

                    Notification::make()->success()->title('Return requested')->send();
                } catch (CourierException $e) {
                    Notification::make()->danger()->title('Steadfast could not take the request')->body($e->getMessage())->send();
                } catch (ConnectionException) {
                    Notification::make()->danger()->title('Could not reach Steadfast')->body('Please try again in a moment.')->send();
                }
            });
    }
}