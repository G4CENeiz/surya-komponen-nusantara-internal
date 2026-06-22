<?php

namespace App\Filament\Accounting\Pages;

use Filament\Pages\Page;

class AccountingDashboard extends Page
{
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-presentation-chart-line';
    protected static ?string $navigationLabel = 'Ringkasan';
    protected static ?int $navigationSort = 1;
    protected static ?string $title = 'Dashboard Dept Keuangan';
    protected static ?string $slug = '';

    protected string $view = 'filament.accounting.pages.accounting-dashboard';

    public $totalBaseSalary = 0;
    public $totalOvertime = 0;
    public $totalAllowance = 0;
    public $totalReimburse = 0;
    public $totalEmployees = 0;
    public $totalExpense = 0;
    public $periodText = '';
    
    public $selectedMonth;
    public $selectedYear;

    public function mount()
    {
        $this->selectedMonth = date('n');
        $this->selectedYear = date('Y');
        $this->loadData();
    }

    public function updatedSelectedMonth()
    {
        $this->loadData();
    }

    public function updatedSelectedYear()
    {
        $this->loadData();
    }

    public function loadData()
    {
        $month = $this->selectedMonth;
        $year = $this->selectedYear;
        $months = [1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'];
        $this->periodText = $months[$month] . ' ' . $year;

        $payslips = \App\Models\Payslip::where('period_month', $month)->where('period_year', $year)->get();
        $this->totalEmployees = \App\Models\Employee::active()->count();

        if ($payslips->count() > 0) {
            $this->totalBaseSalary = $payslips->sum('base_salary');
            $this->totalOvertime = $payslips->sum('overtime_pay');
            $this->totalAllowance = $payslips->sum('total_allowance');
            
            $this->totalReimburse = $payslips->sum(function($p) {
                $comp = $p->components_detail ?? [];
                return $comp['reimbursement'] ?? 0;
            });
        } else {
            $this->totalBaseSalary = 0;
            $this->totalOvertime = 0;
            $this->totalAllowance = 0;
            $this->totalReimburse = 0;
        }

        $this->totalExpense = $this->totalBaseSalary + $this->totalAllowance + $this->totalOvertime + $this->totalReimburse;
    }
}
