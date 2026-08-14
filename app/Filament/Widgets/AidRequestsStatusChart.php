<?php

namespace App\Filament\Widgets;

use App\Domains\AidRequests\Enums\AidRequestStatus;
use App\Domains\AidRequests\Models\AidRequest;
use Filament\Widgets\ChartWidget;

class AidRequestsStatusChart extends ChartWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 2;

    protected ?string $heading = 'توزيع الطلبات حسب الحالة';

    protected ?string $description = 'جميع الطلبات غير المحذوفة';

    protected ?string $maxHeight = '280px';

    private const STATUS_COLORS = [
        AidRequestStatus::SUBMITTED->value => '#0ea5e9',
        AidRequestStatus::UNDER_REVIEW->value => '#eab308',
        AidRequestStatus::NEEDS_INFORMATION->value => '#f97316',
        AidRequestStatus::APPROVED->value => '#22c55e',
        AidRequestStatus::REJECTED->value => '#ef4444',
        AidRequestStatus::DELIVERED->value => '#14b8a6',
        AidRequestStatus::CANCELLED->value => '#6b7280',
    ];

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getData(): array
    {
        $counts = AidRequest::query()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $statuses = [
            AidRequestStatus::SUBMITTED,
            AidRequestStatus::UNDER_REVIEW,
            AidRequestStatus::NEEDS_INFORMATION,
            AidRequestStatus::APPROVED,
            AidRequestStatus::REJECTED,
            AidRequestStatus::DELIVERED,
            AidRequestStatus::CANCELLED,
        ];

        $labels = [];
        $data = [];
        $colors = [];

        foreach ($statuses as $status) {
            $labels[] = $status->label();
            $data[] = (int) ($counts[$status->value] ?? 0);
            $colors[] = self::STATUS_COLORS[$status->value];
        }

        return [
            'labels' => $labels,
            'datasets' => [[
                'label' => 'عدد الطلبات',
                'data' => $data,
                'backgroundColor' => $colors,
            ]],
        ];
    }
}
