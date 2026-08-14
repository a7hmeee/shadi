<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Dashboard;
use App\Filament\Pages\DecisionSupport;
use App\Filament\Widgets\AidOverviewStatsWidget;
use App\Filament\Widgets\AidRequestsStatusChart;
use App\Filament\Widgets\AidRequestsTrendChart;
use App\Filament\Widgets\AidSmartStatsWidget;
use App\Filament\Widgets\AidTypesDistributionChart;
use App\Filament\Widgets\DistributionStatsWidget;
use App\Filament\Widgets\DuplicateAlertsWidget;
use App\Filament\Widgets\PriorityDistributionChart;
use App\Filament\Widgets\RecentAidRequestsWidget;
use App\Filament\Widgets\TopPriorityRequestsWidget;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->brandName('نظام إدارة المساعدات الإنسانية')
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->pages([
                Dashboard::class,
                DecisionSupport::class,
            ])
            ->widgets([
                AccountWidget::class,
                AidOverviewStatsWidget::class,
                AidSmartStatsWidget::class,
                AidRequestsStatusChart::class,
                PriorityDistributionChart::class,
                AidRequestsTrendChart::class,
                TopPriorityRequestsWidget::class,
                DuplicateAlertsWidget::class,
                RecentAidRequestsWidget::class,
                DistributionStatsWidget::class,
                AidTypesDistributionChart::class,
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
