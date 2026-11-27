<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Table;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Facades\Filament;
use Filament\Tables\Columns\TextColumn;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable(),
                TextColumn::make('email')->searchable(),
                TextColumn::make('role')
                    ->badge()
                    ->state(fn (User $record) => $record->roleIn(Filament::getTenant())),
                TextColumn::make('created_at')->label('Added')->date(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->label('Remove from team')
                    ->modalDescription('They will lose access to this store. If they belong to no other store, their account is deleted.')
                    ->using(function (User $record) {
                        $record->stores()->detach(Filament::getTenant()->id);

                        if ($record->stores()->count() === 0) {
                            $record->delete();
                        }

                        return true;
                    }),
            ])
            ->toolbarActions([])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
