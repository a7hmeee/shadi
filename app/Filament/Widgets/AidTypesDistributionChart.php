<?php

namespace App\Filament\Widgets;

use App\Domains\AidRequests\Enums\AidType;
use App\Domains\AidRequests\Models\AidRequest;
use Filament\Widgets\ChartWidget;

class AidTypesDistributionChart extends ChartWidget
{
    protected static ?int $sort = 10;

    protected int|string|array $columnSpan = 2;

    protected ?string $heading = 'أكثر أنواع المساعدات طلبًا';

    protected ?string $description = 'توزيع الطلبات حسب نوع المساعدة';

    protected ?string $maxHeight = '280px';

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $counts = AidRequest::query()
            ->selectRaw('aid_type, count(*) as total')
            ->groupBy('aid_type')
            ->pluck('total', 'aid_type');

        $labels = [];
        $data = [];

        foreach (AidType::cases() as $type) {
            $labels[] = $type->label();
            $data[] = (int) ($counts[$type->value] ?? 0);
        }

        return [
            'labels' => $labels,
            'datasets' => [[
                'label' => 'عدد الطلبات',
                'data' => $data,
                'backgroundColor' => '#0ea5e9',
                'borderRadius' => 6,
            ]],
        ];
    }
}
