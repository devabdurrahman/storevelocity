<?php

namespace App\Filament\Resources\BlockedPhones;

use App\Filament\Resources\BlockedPhones\Pages\CreateBlockedPhone;
use App\Filament\Resources\BlockedPhones\Pages\EditBlockedPhone;
use App\Filament\Resources\BlockedPhones\Pages\ListBlockedPhones;
use App\Filament\Resources\BlockedPhones\Schemas\BlockedPhoneForm;
use App\Filament\Resources\BlockedPhones\Tables\BlockedPhonesTable;
use App\Models\BlockedPhone;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class BlockedPhoneResource extends Resource
{
    protected static ?string $model = BlockedPhone::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoneXMark;

    protected static ?string $recordTitleAttribute = 'phone';

    public static function form(Schema $schema): Schema
    {
        return BlockedPhoneForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BlockedPhonesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBlockedPhones::route('/'),
            'create' => CreateBlockedPhone::route('/create'),
            'edit' => EditBlockedPhone::route('/{record}/edit'),
        ];
    }

    public static function getNavigationLabel(): string
    {
        return 'Blocked phones';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Sales';
    }

    public static function getNavigationSort(): ?int
    {
        return 2;
    }
}
