<?php

namespace App\Filament\Widgets;

use App\Models\Store;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StoreStats extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $store = Filament::getTenant();

        $stats = [
            Stat::make('Orders today', $store->orders()->whereDate('created_at', today())->count()),
            Stat::make('Orders Waiting for confirmation', $store->orders()->where('status', 'pending')->count()),
        ];

        // Money and stock figures are for owners and managers only
        if (auth()->user()->hasRoleIn($store, [Store::ROLE_OWNER, Store::ROLE_MANAGER])) {
            $sales = $store->orders()
                ->where('created_at', '>=', now()->startOfMonth())
                ->whereNotIn('status', ['cancelled', 'awaiting_payment'])
                ->sum('total');

            $stats[] = Stat::make('Sales this month', '৳' . number_format($sales, 2));

            if ($store->hasSteadfast() || config('services.steadfast.fake')) {
                $balance = (new \App\Services\SteadfastClient($store))->cachedBalance();

                $stats[] = Stat::make('Steadfast balance', $balance === null ? 'Unavailable' : '৳' . number_format($balance, 2))
                    ->description('Updated every 10 minutes');
            }
            
            $lowSimple = $store->products()->where('has_variants', false)->where('stock', '<=', 5)->count();

            $lowVariants = \App\Models\ProductVariant::where('is_active', true)
                ->where('stock', '<=', 5)
                ->whereHas('product', fn ($query) => $query->where('store_id', $store->id)->where('has_variants', true))
                ->count();

            $stats[] = Stat::make('Items low on stock', $lowSimple + $lowVariants)
                ->description('5 or fewer left');
        }

        return $stats;
    }
}