<?php

namespace App\Filament\Widgets;

use App\Domains\AidRequests\Models\AidRequest;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class AidRequestsTrendChart extends ChartWidget
{
    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 4;

    protected ?string $heading = 'حركة الطلبات — آخر 7 أيام';

    protected ?string $description = 'عدد الطلبات المقدمة يوميًا حسب تاريخ التقديم';

    protected ?string $maxHeight = '280px';

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        $start = Carbon::now()->subDays(6)->startOfDay();

        $rows = AidRequest::query()
            ->whereNotNull('submitted_at')
            ->where('submitted_at', '>=', $start)
            ->selectRaw('DATE(submitted_at) as day, count(*) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $labels = [];
        $data = [];

        for ($i = 6; $i >= 0; $i--) {
            $day = Carbon::now()->subDays($i)->startOfDay();
            $labels[] = $day->format('d/m');
            $data[] = (int) ($rows[$day->format('Y-m-d')] ?? 0);
        }

        return [
            'labels' => $labels,
            'datasets' => [[
                'label' => 'طلبات مقدمة',
                'data' => $data,
                'borderColor' => '#f59e0b',
                'backgroundColor' => 'rgba(245, 158, 11, 0.12)',
                'fill' => true,
                'tension' => 0.3,
            ]],
        ];
    }
}
