<?php

namespace App\Filament\Widgets;

use App\Domains\AidRequests\Models\AidDistribution;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DistributionStatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 9;

    protected int|string|array $columnSpan = 2;

    protected function getStats(): array
    {
        return [
            Stat::make('عدد المساعدات المسلمة', AidDistribution::count())
                ->description('إجمالي عمليات التسليم')
                ->icon(Heroicon::OutlinedTruck)
                ->color('success'),
            Stat::make('قيمة المساعدات المسلمة', number_format((float) AidDistribution::sum('amount'), 2).' $')
                ->description('مجموع قيم المساعدات')
                ->icon(Heroicon::OutlinedBanknotes)
                ->color('success'),
            Stat::make('سُلمت اليوم', AidDistribution::where('delivered_at', '>=', today())->count())
                ->description('عمليات تسليم اليوم')
                ->icon(Heroicon::OutlinedCalendarDays)
                ->color('info'),
            Stat::make('سُلمت هذا الشهر', AidDistribution::where('delivered_at', '>=', now()->startOfMonth())->count())
                ->description('عمليات تسليم الشهر الحالي')
                ->icon(Heroicon::OutlinedInboxStack)
                ->color('primary'),
        ];
    }
}
