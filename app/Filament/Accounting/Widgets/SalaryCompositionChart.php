<?php

namespace App\Filament\Accounting\Widgets;

use App\Models\Payslip;
use Filament\Widgets\ChartWidget;

class SalaryCompositionChart extends ChartWidget
{
    protected ?string $heading = 'Komposisi Beban Gaji';

    protected static ?int $sort = 1;

    protected function getData(): array
    {
        $payslips = Payslip::where('period_month', now()->month)
            ->where('period_year', now()->year)
            ->get();

        if ($payslips->isEmpty()) {
            return [
                'datasets' => [['data' => []]],
                'labels' => [],
            ];
        }

        // Build deductions from components_detail
        $totalBpjs = 0;
        $totalPph = 0;
        $totalLate = 0;
        foreach ($payslips as $ps) {
            $detail = $ps->components_detail ?? [];
            $totalBpjs += ($detail['ded_bpjs_kes'] ?? 0) + ($detail['ded_bpjs_tk'] ?? 0);
            $totalPph += $detail['ded_pph'] ?? 0;
            $totalLate += $detail['ded_late'] ?? 0;
        }

        return [
            'datasets' => [
                [
                    'data' => [
                        $payslips->sum('base_salary'),
                        $payslips->sum('total_allowance'),
                        $payslips->sum('overtime_pay'),
                        $totalBpjs,
                        $totalPph,
                        $totalLate,
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
