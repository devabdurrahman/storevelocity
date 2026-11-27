<?php

namespace App\Filament\Resources\Orders\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use App\Models\Order;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use App\Models\Store;
use App\Filament\Support\SteadfastActions;
use Filament\Tables\Filters\Filter;
use Illuminate\Support\Str;
use App\Filament\Support\CustomerCheckActions;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('store_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('public_id')
                    ->searchable(),
                TextColumn::make('number')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('customer_name')
                    ->searchable(),
                TextColumn::make('customer_phone')
                    ->searchable(),
                TextColumn::make('payment_method')
                    ->searchable(),
                TextColumn::make('status')
                    ->searchable(),
                TextColumn::make('subtotal')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('shipping_fee')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('total')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('customer_history')
                    ->label('Customer history')
                    ->state(function (Order $record): string {
                        $stats = Order::where('store_id', $record->store_id)
                            ->where('customer_phone', $record->customer_phone)
                            ->selectRaw("count(*) as total, sum(status = 'delivered') as delivered, sum(status = 'cancelled') as cancelled")
                            ->first();

                        return (int) $stats->total . ' orders · '
                            . (int) $stats->delivered . ' delivered · '
                            . (int) $stats->cancelled . ' cancelled';
                    }),
                TextColumn::make('delivery_history')
                    ->label('Delivery history')
                    ->badge()
                    ->placeholder('Not checked')
                    ->state(fn (Order $record) => $record->courierCheck()?->summary())
                    ->color(fn (Order $record) => match ($record->courierCheck()?->level()) {
                        'good' => 'success',
                        'medium' => 'warning',
                        'poor' => 'danger',
                        default => 'gray',
                    })
                    ->wrap(),
                TextColumn::make('items_summary')
                    ->label('Items')
                    ->state(fn (Order $record) => $record->items
                        ->map(fn ($item) => $item->quantity . ' × ' . $item->product_name . ($item->variant_name ? ' (' . $item->variant_name . ')' : ''))
                        ->implode(', '))
                    ->limit(60)
                    ->wrap(),

                TextColumn::make('courier_status')
                    ->label('Courier')
                    ->badge()
                    ->placeholder('Not sent')
                    ->formatStateUsing(fn (?string $state) => $state ? Str::headline($state) : null)
                    ->color(fn (?string $state) => match ($state) {
                        'delivered', 'partial_delivered' => 'success',
                        'cancelled' => 'danger',
                        'hold' => 'warning',
                        null => 'gray',
                        default => 'info',
                    }),

                TextColumn::make('tracking_code')->label('Tracking')->copyable()->toggleable(),
                TextColumn::make('delivery_zone')
                    ->label('Delivery area')
                    ->badge()
                    ->color('gray')
                    ->formatStateUsing(fn (?string $state) => \App\Support\Delivery::label($state))
                    ->toggleable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->modifyQueryUsing(fn ($query) => $query->with('items'))
            ->filters([
                Filter::make('not_sent')
                    ->label('Confirmed, not sent yet')
                    ->query(fn ($query) => $query->where('status', 'confirmed')->whereNull('consignment_id')),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('blockCustomer')
                    ->visible(fn () => auth()->user()->hasRoleIn(\Filament\Facades\Filament::getTenant(), [Store::ROLE_OWNER, Store::ROLE_MANAGER]))
                    ->label('Block customer')
                    ->icon('heroicon-o-no-symbol')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalDescription('This customer will no longer be able to place orders in your store.')
                    ->action(function (Order $record) {
                        $record->store->blockedPhones()->firstOrCreate(
                            ['phone' => $record->customer_phone],
                            ['reason' => 'Blocked from order #' . $record->number],
                        );

                        Notification::make()->title('Customer blocked')->success()->send();
                    }),
                SteadfastActions::book(),
                SteadfastActions::returnRequest(),
                CustomerCheckActions::check(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    SteadfastActions::bulk(),
                    CustomerCheckActions::bulk(),
                ]),
            ]);
    }
}
