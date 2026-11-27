<?php

namespace App\Policies;

use App\Models\Store;
use App\Models\User;
use App\Policies\Concerns\ChecksStoreRole;
use Illuminate\Database\Eloquent\Model;

class ManagerPolicy
{
    use ChecksStoreRole;

    private function allowed(User $user): bool
    {
        return $this->hasRole($user, [Store::ROLE_OWNER, Store::ROLE_MANAGER]);
    }

    public function viewAny(User $user): bool { return $this->allowed($user); }
    public function view(User $user, Model $model): bool { return $this->allowed($user); }
    public function create(User $user): bool { return $this->allowed($user); }
    public function update(User $user, Model $model): bool { return $this->allowed($user); }
    public function delete(User $user, Model $model): bool { return $this->allowed($user); }
    public function deleteAny(User $user): bool { return $this->allowed($user); }
}