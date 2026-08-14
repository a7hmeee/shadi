<?php

namespace App\Filament\Widgets;

use App\Domains\AidRequests\Enums\AidRequestStatus;
use App\Domains\AidRequests\Enums\PriorityLevel;
use App\Domains\AidRequests\Models\AidRequest;
use App\Domains\Identity\Enums\PermissionName;
use App\Filament\Pages\DecisionSupport;
use App\Filament\Resources\AidRequests\AidRequestResource;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AidSmartStatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 4;

    public static function canView(): bool
    {
        return auth()->user()?->can(PermissionName::DECISION_SUPPORT_VIEW->value) ?? false;
    }

    protected function getStats(): array
    {
        $activeStatuses = [
            AidRequestStatus::DELIVERED->value,
            AidRequestStatus::REJECTED->value,
            AidRequestStatus::CANCELLED->value,
        ];

        return [
            Stat::make('الحالات الحرجة', AidRequest::where('priority_level', PriorityLevel::CRITICAL->value)
                ->whereNotIn('status', $activeStatuses)
                ->count())
                ->description('درجة استحقاق 80-100')
                ->url(DecisionSupport::getUrl())
                ->icon(Heroicon::OutlinedExclamationTriangle)
                ->color('danger'),
            Stat::make('أولوية مرتفعة', AidRequest::where('priority_level', PriorityLevel::HIGH->value)
                ->whereNotIn('status', $activeStatuses)
                ->count())
                ->description('درجة استحقاق 60-79')
                ->url(DecisionSupport::getUrl())
                ->icon(Heroicon::OutlinedArrowTrendingUp)
                ->color('warning'),
            Stat::make('اشتباه تكرار', AidRequest::where('possible_duplicate', true)->count())
                ->description('طلبات تحتاج مراجعة التكرار')
                ->url(DecisionSupport::getUrl())
                ->icon(Heroicon::OutlinedMagnifyingGlass)
                ->color('warning'),
            Stat::make('تحتاج معلومات', AidRequest::where('status', AidRequestStatus::NEEDS_INFORMATION->value)->count())
                ->description('بانتظار استكمال البيانات')
                ->url(AidRequestResource::getUrl('index'))
                ->icon(Heroicon::OutlinedQuestionMarkCircle)
                ->color('info'),
        ];
    }
}
