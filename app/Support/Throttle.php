<?php

namespace App\Support;

use Illuminate\Support\Facades\RateLimiter;

class Throttle
{
    /** Counts one action. Returns false when this visitor has done too much in the window. */
    public static function allow(string $action, int $max, int $seconds = 60): bool
    {
        $storeId = app(CurrentStore::class)->get()?->id ?? 'none';
        $key = "act:{$action}:{$storeId}:" . request()->ip();

        if (RateLimiter::tooManyAttempts($key, $max)) {
            return false;
        }

        RateLimiter::hit($key, $seconds);

        return true;
    }
}