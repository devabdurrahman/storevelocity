<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\BlockedPhone;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Policies\ManagerPolicy;
use App\Policies\OrderPolicy;
use Illuminate\Support\Facades\Gate;
use App\Models\Media;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(\App\Support\CurrentStore::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Product::class, ManagerPolicy::class);
        Gate::policy(Category::class, ManagerPolicy::class);
        Gate::policy(BlockedPhone::class, ManagerPolicy::class);
        Gate::policy(Order::class, OrderPolicy::class);
        Gate::policy(Media::class, ManagerPolicy::class);
        // Every store page: one limit per visitor, and one ceiling per store, so a busy or attacked store can't starve the others.
        // The numbers are a starting point. Watch your logs and adjust.
        RateLimiter::for('storefront', function (Request $request) {
            $storeId = $request->attributes->get('store')?->id ?? 'none';

            return [
                Limit::perMinute(240)->by("store:{$storeId}:ip:{$request->ip()}"),
                Limit::perMinute(4000)->by("store:{$storeId}:all"),
            ];
        });
    }
}
