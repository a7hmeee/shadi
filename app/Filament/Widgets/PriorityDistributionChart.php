<?php

namespace App\Filament\Widgets;

use App\Domains\AidRequests\Enums\PriorityLevel;
use App\Domains\AidRequests\Models\AidRequest;
use Filament\Widgets\ChartWidget;

class PriorityDistributionChart extends ChartWidget
{
    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 2;

    protected ?string $heading = 'توزيع مستويات الأولوية';

    protected ?string $description = 'حسب درجات الاستحقاق المحسوبة';

    protected ?string $maxHeight = '280px';

    private const LEVEL_COLORS = [
        PriorityLevel::LOW->value => '#6b7280',
        PriorityLevel::MEDIUM->value => '#0ea5e9',
        PriorityLevel::HIGH->value => '#eab308',
        PriorityLevel::CRITICAL->value => '#ef4444',
    ];

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getData(): array
    {
        $counts = AidRequest::query()
            ->selectRaw('priority_level, count(*) as total')
            ->groupBy('priority_level')
            ->pluck('total', 'priority_level');

        $labels = [];
        $data = [];
        $colors = [];

        foreach (PriorityLevel::cases() as $level) {
            $labels[] = $level->label();
            $data[] = (int) ($counts[$level->value] ?? 0);
            $colors[] = self::LEVEL_COLORS[$level->value];
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
