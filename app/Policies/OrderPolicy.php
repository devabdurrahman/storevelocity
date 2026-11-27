<?php

namespace App\Policies;

use App\Models\Store;
use App\Models\User;
use App\Policies\Concerns\ChecksStoreRole;
use Illuminate\Database\Eloquent\Model;

class OrderPolicy
{
    use ChecksStoreRole;

    private function everyone(User $user): bool
    {
        return $this->hasRole($user, [Store::ROLE_OWNER, Store::ROLE_MANAGER, Store::ROLE_STAFF]);
    }

    public function viewAny(User $user): bool { return $this->everyone($user); }
    public function view(User $user, Model $model): bool { return $this->everyone($user); }
    public function update(User $user, Model $model): bool { return $this->everyone($user); }

    // Orders come from the storefront only
    public function create(User $user): bool { return false; }

    public function delete(User $user, Model $model): bool
    {
        return $this->hasRole($user, [Store::ROLE_OWNER]);
    }

    public function deleteAny(User $user): bool
    {
        return $this->hasRole($user, [Store::ROLE_OWNER]);
    }
}