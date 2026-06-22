<?php

namespace App\Filament\Accounting\Widgets;

use Filament\Widgets\ChartWidget;

class SalaryExpenseChart extends ChartWidget
{
    protected ?string $heading = 'Komposisi Beban Gaji (Juni 2026)';

    protected static ?int $sort = 2;

    protected ?string $maxHeight = '300px';

    protected function getData(): array
    {
        return [
            'datasets' => [
                [
                    'label' => 'Beban Penggajian',
                    'data' => [425500000, 32400000, 15000000, 5000000],
                    'backgroundColor' => [
                        '#3b82f6', // blue-500 for Gaji Pokok
                        '#f97316', // orange-500 for Lembur
                        '#10b981', // emerald-500 for BPJS Kesehatan
                        '#8b5cf6', // violet-500 for BPJS Ketenagakerjaan
                    ],
                ],
            ],
            'labels' => ['Gaji Pokok', 'Honor Lembur', 'BPJS Kesehatan (Perusahaan)', 'BPJS TK (Perusahaan)'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
