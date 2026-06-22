<?php

namespace App\Filament\Accounting\Widgets;

use App\Models\Payroll;
use App\Models\PayrollPeriod;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AccountingStatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 0;

    protected function getStats(): array
    {
        $currentPeriod = PayrollPeriod::where('month', now()->month)
            ->where('year', now()->year)
            ->first();

        $payrolls = $currentPeriod ? Payroll::where('payroll_period_id', $currentPeriod->id)->get() : collect();

        $totalBaseSalary = $payrolls->sum('base_salary');
        $totalOvertimePay = $payrolls->sum('overtime_pay');
        $totalDeductions = $payrolls->sum(fn ($p) => $p->bpjs_health + $p->bpjs_employment + $p->pph21 + $p->tardiness_deduction + $p->other_deductions);
        $totalTHP = $payrolls->sum('take_home_pay');
        $employeeCount = $payrolls->count();

        return [
            Stat::make('Periode Aktif', $currentPeriod?->name ?? 'Belum ada')
                ->description($currentPeriod ? "{$employeeCount} pegawai" : 'Buat periode terlebih dahulu')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('info'),

            Stat::make('Total Gaji Pokok', 'Rp '.number_format($totalBaseSalary, 0, ',', '.'))
                ->description('Gaji pokok keseluruhan')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('primary'),

            Stat::make('Total Honor Lembur', 'Rp '.number_format($totalOvertimePay, 0, ',', '.'))
                ->description('Lembur tervalidasi')
                ->descriptionIcon('heroicon-m-clock')
                ->color('success'),

            Stat::make('Total Potongan', 'Rp '.number_format($totalDeductions, 0, ',', '.'))
                ->description('BPJS + PPh21 + Keterlambatan')
                ->descriptionIcon('heroicon-m-minus-circle')
                ->color('danger'),

            Stat::make('Total Take-Home Pay', 'Rp '.number_format($totalTHP, 0, ',', '.'))
                ->description('Dibayarkan ke pegawai')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color($totalTHP > 0 ? 'success' : 'gray'),
        ];
    }
}
