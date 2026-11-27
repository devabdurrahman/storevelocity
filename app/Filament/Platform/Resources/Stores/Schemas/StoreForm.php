<?php

namespace App\Filament\Platform\Resources\Stores\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class StoreForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')->required()->maxLength(100),
                TextInput::make('slug')
                    ->disabled()
                    ->dehydrated(false),
                Toggle::make('is_active')
                    ->label('Storefront online')
                    ->helperText('Turn off to take the storefront offline. The owner can still log in.'),
            ]);
    }
}
