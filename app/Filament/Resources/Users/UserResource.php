<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\Schemas\UserForm;
use App\Filament\Resources\Users\Tables\UsersTable;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use App\Models\Store;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationLabel = 'Team';
    protected static ?string $modelLabel = 'team member';
    protected static ?string $pluralModelLabel = 'team';

    // Users aren't "owned" by a store through a store() relation
    protected static bool $isScopedToTenant = false;

    public static function form(Schema $schema): Schema
    {
        return UserForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UsersTable::configure($table);
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
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $store = Filament::getTenant();

        // Only members of the CURRENT store can ever be loaded here
        return parent::getEloquentQuery()
            ->whereHas('stores', fn ($query) => $query->where('stores.id', $store->id));
    }

    protected static function isOwner(): bool
    {
        $store = Filament::getTenant();

        return $store && auth()->user()?->hasRoleIn($store, [Store::ROLE_OWNER]);
    }

    protected static function isTeamMember(Model $record): bool
    {
        return in_array($record->roleIn(Filament::getTenant()), [Store::ROLE_MANAGER, Store::ROLE_STAFF], true);
    }

    public static function canViewAny(): bool { return static::isOwner(); }
    public static function canCreate(): bool { return static::isOwner(); }
    public static function canEdit(Model $record): bool { return static::isOwner() && static::isTeamMember($record); }
    public static function canDelete(Model $record): bool { return static::isOwner() && static::isTeamMember($record); }
    public static function canDeleteAny(): bool { return false; }

    public static function getNavigationGroup(): ?string
    {
        return 'Settings';
    }

    public static function getNavigationSort(): ?int
    {
        return 2;
    }
}
