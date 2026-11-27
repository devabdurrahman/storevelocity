<?php

namespace App\Policies\Concerns;

use App\Models\Store;
use App\Models\User;
use Filament\Facades\Filament;

trait ChecksStoreRole
{
    protected function hasRole(User $user, array $roles): bool
    {
        $store = Filament::getTenant();

        return $store instanceof Store && $user->hasRoleIn($store, $roles);
    }
}