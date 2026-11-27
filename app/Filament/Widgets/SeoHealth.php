<?php

namespace App\Filament\Widgets;

use App\Models\Store;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SeoHealth extends StatsOverviewWidget
{
    public static function canView(): bool
    {
        $store = Filament::getTenant();

        return $store instanceof Store
            && auth()->user()?->hasRoleIn($store, [Store::ROLE_OWNER, Store::ROLE_MANAGER]);
    }

    protected function getStats(): array
    {
        $store = Filament::getTenant();

        $visible = $store->visibleProducts();

        $noDescription = (clone $visible)
            ->where(fn ($query) => $query->whereNull('description')->orWhere('description', ''))
            ->count();

        $noPhotos = (clone $visible)
            ->where(fn ($query) => $query->whereNull('images')->orWhereJsonLength('images', 0))
            ->count();

        $indexable = (bool) (($store->seo ?? [])['indexable'] ?? false);

        return [
            Stat::make('Visible to Google', $indexable ? 'Yes' : 'No')
                ->description($indexable ? 'Search engines can list your store' : 'Turn it on in Store settings when you are ready')
                ->color($indexable ? 'success' : 'danger'),

            Stat::make('Products without a description', $noDescription)
                ->description('Descriptions help customers and Google')
                ->color($noDescription ? 'warning' : 'success'),

            Stat::make('Products without photos', $noPhotos)
                ->description('Photos make people click')
                ->color($noPhotos ? 'warning' : 'success'),
        ];
    }
}