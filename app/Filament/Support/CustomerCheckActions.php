<?php

namespace App\Filament\Support;

use App\Exceptions\CourierException;
use App\Models\Order;
use App\Models\Store;
use App\Services\SteadfastClient;
use App\Support\CustomerCheck;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Client\ConnectionException;

class CustomerCheckActions
{
    private static function visible(): bool
    {
        $store = Filament::getTenant();

        return $store instanceof Store
            && app(CustomerCheck::class)->enabled()
            && auth()->user()?->hasRoleIn($store, [Store::ROLE_OWNER, Store::ROLE_MANAGER]);
    }

    public static function check(): Action
    {
        return Action::make('checkCustomer')
            ->label(fn (Order $record) => $record->courierCheck() ? 'Recheck customer' : 'Check customer')
            ->icon('heroicon-o-shield-check')
            ->color('gray')
            ->visible(fn () => self::visible())
            ->action(function (Order $record) {
                try {
                    // A second click on a saved result refreshes it
                    $result = app(CustomerCheck::class)->check(
                        $record->store,
                        $record->customer_phone,
                        force: $record->courierCheck() !== null,
                    );

                    $notification = Notification::make()
                        ->title('Delivery history for ' . $record->customer_phone)
                        ->body($result->summary() . '. Based on this customer\'s earlier parcels with Steadfast. Use it as guidance, not as a verdict.');

                    match ($result->level()) {
                        'good' => $notification->success(),
                        'medium' => $notification->warning(),
                        'poor' => $notification->danger(),
                        default => $notification->info(),
                    };

                    $notification->send();
                } catch (CourierException $e) {
                    Notification::make()->danger()->title('Could not check this customer')->body($e->getMessage())->send();
                } catch (ConnectionException) {
                    Notification::make()->danger()->title('Could not reach Steadfast')->body('Please try again in a moment.')->send();
                }
            });
    }

    public static function bulk(): BulkAction
    {
        return BulkAction::make('bulkCheckCustomers')
            ->label('Check selected customers')
            ->icon('heroicon-o-shield-check')
            ->requiresConfirmation()
            ->modalDescription('Looks up each different customer once, up to 25 at a time. Results already checked in the last 24 hours are reused.')
            ->visible(fn () => self::visible())
            ->deselectRecordsAfterCompletion()
            ->action(function (Collection $records) {
                $service = app(CustomerCheck::class);
                $checked = 0;
                $failed = [];

                foreach ($records->unique('customer_phone')->take(25) as $order) {
                    try {
                        $service->check($order->store, $order->customer_phone);
                        $checked++;
                    } catch (CourierException $e) {
                        $failed[] = $order->customer_phone . ': ' . $e->getMessage();

                        // Wrong keys or a missing address would fail every remaining customer too
                        if (in_array($e->getMessage(), [SteadfastClient::AUTH_FAILED, 'The Steadfast fraud check address is not set up yet.'], true)) {
                            break;
                        }
                    } catch (ConnectionException) {
                        $failed[] = $order->customer_phone . ': could not reach Steadfast';
                    }

                    usleep(200000); // a short pause between lookups
                }

                $notification = Notification::make()
                    ->title('Customer checks finished')
                    ->body($checked . ' checked.' . ($failed ? ' ' . count($failed) . ' failed: ' . implode('; ', array_slice($failed, 0, 3)) : ''));

                ($failed ? $notification->warning() : $notification->success())->send();
            });
    }
}