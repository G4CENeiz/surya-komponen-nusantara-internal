<?php

namespace App\Filament\Accounting\Widgets;

use App\Models\Payroll;
use App\Models\PayrollPeriod;
use Filament\Widgets\ChartWidget;

class SalaryCompositionChart extends ChartWidget
{
    protected ?string $heading = 'Komposisi Beban Gaji';

    protected static ?int $sort = 1;

    protected function getData(): array
    {
        $currentPeriod = PayrollPeriod::where('month', now()->month)
            ->where('year', now()->year)
            ->first();

        if (! $currentPeriod) {
            return [
                'datasets' => [['data' => []]],
                'labels' => [],
            ];
        }

        $payrolls = Payroll::where('payroll_period_id', $currentPeriod->id)->get();

        return [
            'datasets' => [
                [
                    'data' => [
                        $payrolls->sum('base_salary'),
                        $payrolls->sum('allowance'),
                        $payrolls->sum('overtime_pay'),
                        $payrolls->sum('bpjs_health') + $payrolls->sum('bpjs_employment'),
                        $payrolls->sum('pph21'),
                        $payrolls->sum('tardiness_deduction'),
                    ],
                    'backgroundColor' => [
                        '#3b82f6',
                        '#22c55e',
                        '#f59e0b',
                        '#ef4444',
                        '#8b5cf6',
                        '#ec4899',
                    ],
                ],
            ],
            'labels' => [
                'Gaji Pokok',
                'Tunjangan',
                'Honor Lembur',
                'BPJS',
                'PPh 21',
                'Potongan Keterlambatan',
            ],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
