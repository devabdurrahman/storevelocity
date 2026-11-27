<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Schemas\Schema;
use App\Models\Store;
use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')->required()->maxLength(100),

            TextInput::make('email')
                ->label('Email (used to log in)')
                ->email()
                ->required()
                ->maxLength(255)
                ->unique(User::class, 'email', ignoreRecord: true)
                ->disabled(fn (string $operation) => $operation === 'edit')
                ->dehydrated(fn (string $operation) => $operation === 'create'),

            Select::make('role')
                ->options([
                    Store::ROLE_MANAGER => 'Manager (products, categories, orders, blocked phones)',
                    Store::ROLE_STAFF => 'Order staff (orders only)',
                ])
                ->required(),

            TextInput::make('password')
                ->password()
                ->revealable()
                ->minLength(8)
                ->required(fn (string $operation) => $operation === 'create')
                ->dehydrated(fn (?string $state) => filled($state))
                ->helperText('At least 8 characters. When editing, leave blank to keep the current password.'),
                        ]);
    }
}
