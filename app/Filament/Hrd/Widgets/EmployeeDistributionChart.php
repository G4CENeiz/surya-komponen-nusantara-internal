<?php

namespace App\Filament\Hrd\Widgets;

use App\Models\Department;
use Filament\Widgets\ChartWidget;

class EmployeeDistributionChart extends ChartWidget
{
    protected ?string $heading = 'Employee Distribution by Department';

    protected static ?int $sort = 1;

    protected ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $departments = Department::withCount('employees')
            ->orderByDesc('employees_count')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Employees',
                    'data' => $departments->pluck('employees_count')->toArray(),
                    'backgroundColor' => '#3b82f6',
                    'hoverBackgroundColor' => '#2563eb',
                ],
            ],
            'labels' => $departments->pluck('name')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
