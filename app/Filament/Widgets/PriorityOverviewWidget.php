<?php

namespace App\Filament\Widgets;

use App\Domains\AidRequests\Enums\PriorityLevel;
use App\Domains\AidRequests\Models\AidRequest;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PriorityOverviewWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        return [
            Stat::make('الطلبات الحرجة', AidRequest::where('priority_level', PriorityLevel::CRITICAL->value)->count())
                ->description('طلبات بدرجة أولوية حرجة (80-100)')
                ->color('danger')
                ->icon('heroicon-o-exclamation-triangle'),
            Stat::make('الأولوية المرتفعة', AidRequest::where('priority_level', PriorityLevel::HIGH->value)->count())
                ->description('طلبات بدرجة أولوية مرتفعة (60-79)')
                ->color('warning')
                ->icon('heroicon-o-arrow-trending-up'),
            Stat::make('طلبات مشتبه بتكرارها', AidRequest::where('possible_duplicate', true)->count())
                ->description('طلبات محتمل تكرارها وتحتاج مراجعة')
                ->color('danger')
                ->icon('heroicon-o-magnifying-glass'),
        ];
    }
}
