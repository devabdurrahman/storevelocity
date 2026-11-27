<?php

namespace App\Filament\Platform\Resources\Stores\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Tables\Filters\TernaryFilter;

class StoresTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable(),
                TextColumn::make('slug')->searchable(),
                IconColumn::make('is_active')->label('Online')->boolean(),
                TextColumn::make('users_count')->counts('users')->label('Users'),
                TextColumn::make('products_count')->counts('products')->label('Products'),
                TextColumn::make('orders_count')->counts('orders')->label('Orders'),
                TextColumn::make('created_at')->label('Created')->date()->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                TernaryFilter::make('is_active')->label('Online'),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('openStorefront')
                    ->label('Open storefront')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (\App\Models\Store $record) => $record->storefrontUrl(), shouldOpenInNewTab: true),
            ])
            ->toolbarActions([]);
    }
}
