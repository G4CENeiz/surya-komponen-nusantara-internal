<?php

namespace App\Providers\Filament;

use App\Filament\Hrd\Pages\HrdDashboard;
use App\Filament\Hrd\Widgets\EmployeeDistributionChart;
use App\Filament\Hrd\Widgets\HrdOverview;
use App\Filament\Hrd\Widgets\LatestEmployeesWidget;
use App\Filament\Hrd\Widgets\RecentAnnouncementsWidget;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class HrdPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('hrd')
            ->path('hrd')
            ->login()
            ->brandName('SKN — HRD Panel')
            ->favicon(asset('favicon.ico'))
            ->darkMode()
            ->colors([
                'primary' => Color::Blue,
            ])
            ->darkMode(false)
            ->sidebarWidth('14rem')
            ->sidebarCollapsibleOnDesktop()
            ->font('Instrument Sans')
            ->discoverResources(in: app_path('Filament/Hrd/Resources'), for: 'App\Filament\Hrd\Resources')
            ->discoverPages(in: app_path('Filament/Hrd/Pages'), for: 'App\Filament\Hrd\Pages')
            ->pages([
                HrdDashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Hrd/Widgets'), for: 'App\Filament\Hrd\Widgets')
            ->widgets([
                HrdOverview::class,
                EmployeeDistributionChart::class,
                LatestEmployeesWidget::class,
                RecentAnnouncementsWidget::class,
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
            ->plugins([
                FilamentShieldPlugin::make(),
            ])
            ->renderHook(
                PanelsRenderHook::AUTH_LOGIN_FORM_AFTER,
                fn () => view('filament.components.panel-switcher'),
            )
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
