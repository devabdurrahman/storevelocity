<?php

namespace App\Support;

use App\Models\Store;

class CurrentStore
{
    private ?Store $store = null;
    private bool $resolved = false;

    public function get(): ?Store
    {
        if (! $this->resolved) {
            $this->resolved = true;

            $host = request()->getHost();
            $suffix = '.' . config('app.central_domain');

            if (str_ends_with($host, $suffix)) {
                $slug = substr($host, 0, -strlen($suffix));

                $this->store = Store::where('slug', $slug)
                    ->where('is_active', true)
                    ->first();
            }
        }

        return $this->store;
    }

    public function getOrFail(): Store
    {
        return $this->get() ?? abort(404);
    }
}