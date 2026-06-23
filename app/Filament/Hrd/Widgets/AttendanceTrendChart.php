<?php

namespace App\Filament\Hrd\Widgets;

use App\Models\Attendance;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class AttendanceTrendChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected ?string $heading = 'Tren Kehadiran';

    protected static ?int $sort = 4;

    protected ?string $maxHeight = '300px';

    protected int|string|array $columnSpan = 'half';

    protected function getData(): array
    {
        $dateFrom = $this->pageFilters['dateFrom'] ?? null;
        $dateTo = $this->pageFilters['dateTo'] ?? null;

        $start = $dateFrom ? Carbon::parse($dateFrom) : now()->subDays(13);
        $end = $dateTo ? Carbon::parse($dateTo) : now();

        $days = collect();
        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            $days->push($date->copy()->toDateString());
        }

        $approvedCounts = Attendance::where('status', 'approved')
            ->whereIn('date', $days)
            ->selectRaw('date, count(*) as total')
            ->groupBy('date')
            ->pluck('total', 'date');

        $rejectedCounts = Attendance::where('status', 'rejected')
            ->whereIn('date', $days)
            ->selectRaw('date, count(*) as total')
            ->groupBy('date')
            ->pluck('total', 'date');

        $labels = $days->map(fn ($d) => Carbon::parse($d)->format('M d'))->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Disetujui',
                    'data' => $days->map(fn ($d) => $approvedCounts->get($d, 0))->toArray(),
                    'borderColor' => '#22c55e',
                    'backgroundColor' => 'rgba(34, 197, 94, 0.1)',
                    'fill' => true,
                    'tension' => 0.4,
                    'pointRadius' => 3,
                    'pointHoverRadius' => 6,
                ],
                [
                    'label' => 'Ditolak',
                    'data' => $days->map(fn ($d) => $rejectedCounts->get($d, 0))->toArray(),
                    'borderColor' => '#ef4444',
                    'backgroundColor' => 'rgba(239, 68, 68, 0.1)',
                    'fill' => true,
                    'tension' => 0.4,
                    'pointRadius' => 3,
                    'pointHoverRadius' => 6,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
