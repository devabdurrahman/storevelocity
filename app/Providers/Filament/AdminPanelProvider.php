<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
// use Filament\Widgets\AccountWidget;
// use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\Navigation\NavigationItem;
use Filament\FontProviders\GoogleFontProvider;
use App\Support\BrandColor;
use App\Models\Store;
use Filament\Facades\Filament;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login(\App\Filament\Pages\Auth\Login::class)
            ->registration(\App\Filament\Pages\Auth\Register::class)
            ->profile()
            ->tenant(\App\Models\Store::class, slugAttribute: 'slug')
            ->tenantProfile(\App\Filament\Pages\Tenancy\EditStoreProfile::class)
            ->tenantRegistration(\App\Filament\Pages\Tenancy\RegisterStore::class)
            // ->navigationItems([
            //     NavigationItem::make('View storefront')
            //         ->icon('heroicon-o-arrow-top-right-on-square')
            //         ->url(fn (): string => \Filament\Facades\Filament::getTenant()->storefrontUrl(), shouldOpenInNewTab: true)
            //         ->sort(100),
            // ])
            ->navigationGroups(['Sales', 'Catalog', 'Settings'])
            ->unsavedChangesAlerts()
            ->navigationItems([
                NavigationItem::make('Store settings')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->group('Settings')
                    ->sort(1)
                    ->url(fn () => Filament::getTenantProfileUrl())
                    ->visible(fn () => ($tenant = Filament::getTenant())
                        && auth()->user()?->hasRoleIn($tenant, [Store::ROLE_OWNER])),

                NavigationItem::make('View storefront')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->group('Settings')
                    ->url(fn (): string => Filament::getTenant()->storefrontUrl(), shouldOpenInNewTab: true)
                    ->sort(100),
            ])
            ->colors([
                'primary' => BrandColor::TEAL,
            ])
            ->font('Anek Bangla', provider: GoogleFontProvider::class)
            ->brandName(config('app.name'))
            ->brandLogo(fn () => view('filament.brand'))
            ->brandLogoHeight('2rem')
            ->favicon(asset('favicon.svg'))
            // ->sidebarCollapsibleOnDesktop()
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                // AccountWidget::class,
                // FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
