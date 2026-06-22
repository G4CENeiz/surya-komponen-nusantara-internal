<?php

namespace App\Filament\Hrd\Widgets;

use App\Models\Department;
use Filament\Widgets\ChartWidget;

class EmployeeDistributionChart extends ChartWidget
{
    protected ?string $heading = 'Employees by Department';

    protected static ?int $sort = 1;

    protected ?string $maxHeight = '300px';

    protected int|string|array $columnSpan = 'half';

    protected ?string $type = 'doughnut';

    private const COLORS = [
        '#3b82f6', // blue
        '#22c55e', // green
        '#f59e0b', // amber
        '#ef4444', // red
        '#8b5cf6', // violet
        '#06b6d4', // cyan
        '#f97316', // orange
        '#ec4899', // pink
    ];

    protected function getData(): array
    {
        $departments = Department::withCount('employees')
            ->orderByDesc('employees_count')
            ->get();

        return [
            'datasets' => [
                [
                    'data' => $departments->pluck('employees_count')->toArray(),
                    'backgroundColor' => array_slice(self::COLORS, 0, $departments->count()),
                    'hoverBackgroundColor' => array_slice(self::COLORS, 0, $departments->count()),
                    'borderWidth' => 0,
                    'hoverOffset' => 8,
                ],
            ],
            'labels' => $departments->pluck('name')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return $this->type ?? 'doughnut';
    }
}
