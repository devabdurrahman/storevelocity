<?php

namespace App\Filament\Platform\Widgets;

use App\Models\Order;
use App\Models\Store;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PlatformStats extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $since = now()->subDays(30);

        $orders = Order::where('created_at', '>=', $since);
        $value = (clone $orders)->where('status', '!=', 'cancelled')->sum('total');

        return [
            Stat::make('Stores', Store::count())
                ->description(Store::where('is_active', true)->count() . ' online'),
            Stat::make('Orders (30 days)', (clone $orders)->count()),
            Stat::make('Order value (30 days)', '৳' . number_format($value, 2))
                ->description('Excluding cancelled orders'),
        ];
    }
}
