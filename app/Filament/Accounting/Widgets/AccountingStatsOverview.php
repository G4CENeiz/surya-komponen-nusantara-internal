<?php

namespace App\Filament\Accounting\Widgets;

use App\Models\Payslip;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AccountingStatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 0;

    protected function getStats(): array
    {
        $payslips = Payslip::where('period_month', now()->month)
            ->where('period_year', now()->year)
            ->get();

        $totalBaseSalary = $payslips->sum('base_salary');
        $totalOvertimePay = $payslips->sum('overtime_pay');
        $totalAllowance = $payslips->sum('total_allowance');
        $totalDeductions = $payslips->sum('total_deduction');
        $totalTHP = $payslips->sum('net_salary');
        $employeeCount = $payslips->count();

        return [
            Stat::make('Total Slip Gaji', $employeeCount)
                ->description('Periode '.now()->translatedFormat('F Y'))
                ->descriptionIcon('heroicon-m-document-text')
                ->color('info'),

            Stat::make('Total Gaji Pokok', 'Rp '.number_format($totalBaseSalary, 0, ',', '.'))
                ->description('Gaji pokok keseluruhan')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('primary'),

            Stat::make('Total Tunjangan + Lembur', 'Rp '.number_format($totalAllowance + $totalOvertimePay, 0, ',', '.'))
                ->description('Tunjangan: Rp '.number_format($totalAllowance, 0, ',', '.'))
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
