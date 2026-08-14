<?php

namespace App\Filament\Widgets;

use App\Domains\AidRequests\Enums\AidRequestStatus;
use App\Domains\AidRequests\Models\AidRequest;
use App\Domains\Beneficiaries\Models\Beneficiary;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AidOverviewStatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 4;

    protected function getStats(): array
    {
        return [
            Stat::make('إجمالي المستفيدين', Beneficiary::where('is_active', true)->count())
                ->description('مستفيدون نشطون')
                ->icon(Heroicon::OutlinedUsers)
                ->color('primary'),
            Stat::make('إجمالي الطلبات', AidRequest::count())
                ->description('جميع طلبات المساعدة')
                ->icon(Heroicon::OutlinedClipboardDocumentList)
                ->color('primary'),
            Stat::make('طلبات جديدة', AidRequest::where('status', AidRequestStatus::SUBMITTED)->count())
                ->description('بانتظار بدء المراجعة')
                ->icon(Heroicon::OutlinedInboxArrowDown)
                ->color('info'),
            Stat::make('قيد المراجعة', AidRequest::where('status', AidRequestStatus::UNDER_REVIEW)->count())
                ->description('جارٍ مراجعتها حاليًا')
                ->icon(Heroicon::OutlinedMagnifyingGlass)
                ->color('warning'),
            Stat::make('تمت الموافقة', AidRequest::where('status', AidRequestStatus::APPROVED)->count())
                ->description('بانتظار التسليم')
                ->icon(Heroicon::OutlinedCheckCircle)
                ->color('success'),
            Stat::make('تم التسليم', AidRequest::where('status', AidRequestStatus::DELIVERED)->count())
                ->description('مساعدة وصلت المستفيد')
                ->icon(Heroicon::OutlinedTruck)
                ->color('success'),
        ];
    }
}
