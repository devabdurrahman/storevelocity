<?php

namespace App\Filament\Resources\BlockedPhones\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Facades\Filament;
use Illuminate\Validation\Rules\Unique;

class BlockedPhoneForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('phone')
                    ->required()
                    ->tel()
                    ->regex('/^01[3-9]\d{8}$/')
                    ->helperText('11 digits, starting with 01 (for example 01712345678).')
                    ->unique(
                        ignoreRecord: true,
                        modifyRuleUsing: fn (Unique $rule) => $rule->where('store_id', Filament::getTenant()->id),
                    ),

                TextInput::make('reason')->maxLength(255),
            ]);
    }
}
