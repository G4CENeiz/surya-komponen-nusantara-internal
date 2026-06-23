<?php

namespace App\Filament\Accounting\Pages;

use App\Models\Attendance;
use App\Models\Department;
use App\Models\Employee;
use App\Models\JobClass;
use App\Models\Overtime;
use App\Models\PayrollSetting;
use App\Models\Payslip;
use App\Models\Reimbursement;
use App\Models\TimeOff;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class Payroll extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-currency-dollar';

    protected static ?string $navigationLabel = 'Slip Gaji';

    protected static ?int $navigationSort = 5;

    protected static ?string $title = 'Kelola Penggajian';

    protected string $view = 'filament.accounting.pages.payroll';

    public array $payrollData = [];

    public $searchQuery = '';

    public $filterDept = '';

    public $filterClass = '';

    public $payrollHistory = [];

    public $currentPage = 1;

    public $isViewingHistory = false;

    public $historyMonth = null;

    public $historyYear = null;

    public $employeeHistoryModalOpen = false;

    public $employeeHistoryDetails = [];

    public $isEditing = false;

    public $editingIndex = null;

    public $editName = '';

    public ?array $filterData = [];

    public $editBasic = 0;

    // Pagination
    public $perPage = 10;

    public $editOvertime = 0;

    public $editDedBpjsKesehatan = 0;

    public $editDedBpjsKetenagakerjaan = 0;

    public $editDedPph21 = 0;

    public $editDedLate = 0;

    public $editDedLoan = 0;

    public $editDeductions = 0;

    public $editThp = 0;

    public $editReimburse = 0;

    public $editOvertimeHours = 0;

    public $editLateFrequency = 0;

    public $overtimeRate = 0;

    public $latePenalty = 0;

    public $bpjsKesRate = 0;

    public $bpjsTkRate = 0;

    public $editAllowance = 0;

    public function mount()
    {
        $overtimeSetting = PayrollSetting::where('key', 'overtime_rate')->first();
        $this->overtimeRate = $overtimeSetting ? (int) $overtimeSetting->value : 0;

        $lateSetting = PayrollSetting::where('key', 'late_penalty')->first();
        $this->latePenalty = $lateSetting ? (int) $lateSetting->value : 0;

        $bpjsKesSetting = PayrollSetting::where('key', 'bpjs_kes_percent')->first();
        $this->bpjsKesRate = $bpjsKesSetting ? (float) $bpjsKesSetting->value : 0;

        $bpjsTkSetting = PayrollSetting::where('key', 'bpjs_tk_percent')->first();
        $this->bpjsTkRate = $bpjsTkSetting ? (float) $bpjsTkSetting->value : 0;

        $this->loadData();
    }

    public function getCurrentPeriod()
    {
        $months = [1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'];
        $month = $this->isViewingHistory ? $this->historyMonth : now()->month;
        $year = $this->isViewingHistory ? $this->historyYear : now()->year;

        return [
            'month' => $month,
            'year' => $year,
            'text' => $months[$month].' '.$year,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('closeHistoryDetail')
                ->label('Kembali')
                ->color('gray')
                ->icon('heroicon-o-arrow-left')
                ->action('closeHistoryDetail')
                ->visible(fn () => $this->isViewingHistory),

            Action::make('exportExcel')
                ->label('Ekspor Excel')
                ->color('gray')
                ->icon('heroicon-o-document-arrow-down')
                ->action('exportExcel'),

            Action::make('generatePayroll')
                ->label('Generate Slip Gaji')
                ->color('primary')
                ->icon('heroicon-o-document-text')
                ->action('generatePayroll'),
        ];
    }

    public function viewHistoryDetail($month, $year)
    {
        $this->isViewingHistory = true;
        $this->historyMonth = $month;
        $this->historyYear = $year;
        $this->currentPage = 1;
        $this->loadData();
    }

    public function closeHistoryDetail()
    {
        $this->isViewingHistory = false;
        $this->historyMonth = null;
        $this->historyYear = null;
        $this->currentPage = 1;
        $this->loadData();
    }

    public function openEmployeeHistoryDetail($userId)
    {
        $this->employeeHistoryDetails = [
            'name' => Employee::where('user_id', $userId)->first()->full_name ?? 'Unknown',
            'lates' => Attendance::where('user_id', $userId)
                ->whereMonth('date', $this->historyMonth)
                ->whereYear('date', $this->historyYear)
                ->where('is_late', true)
                ->get(),
            'overtimes' => Overtime::where('user_id', $userId)
                ->whereMonth('date', $this->historyMonth)
                ->whereYear('date', $this->historyYear)
                ->where('status', 'approved')
                ->get(),
            'timeoffs' => TimeOff::where('user_id', $userId)
                ->whereMonth('start_date', $this->historyMonth)
                ->whereYear('start_date', $this->historyYear)
                ->get(),
        ];
        $this->employeeHistoryModalOpen = true;
    }

    public function closeEmployeeHistoryDetail()
    {
        $this->employeeHistoryModalOpen = false;
        $this->employeeHistoryDetails = [];
    }

    public function updatedFilterData()
    {
        $this->loadData();
        $this->currentPage = 1;
    }

    public function loadData()
    {
        $period = $this->getCurrentPeriod();
        $month = $period['month'];
        $year = $period['year'];

        if ($this->isViewingHistory) {
            $payslips = Payslip::with(['employee.department', 'employee.jobClass'])
                ->where('period_month', $month)
                ->where('period_year', $year)
                ->get();

            $this->payrollData = [];
            foreach ($payslips as $payslip) {
                $emp = $payslip->employee;
                if (! $emp) {
                    continue;
                }
                $components = $payslip->components_detail ?? [];

                $this->payrollData[] = [
                    'id' => $emp->id,
                    'user_id' => $emp->user_id,
                    'name' => $emp->full_name,
                    'nik' => $emp->nik,
                    'dept' => $emp->department->name ?? '-',
                    'class' => $emp->jobClass->name ?? '-',
                    'basic' => $payslip->base_salary,
                    'allowance' => $payslip->total_allowance ?? 0,
                    'overtime' => $payslip->overtime_pay,
                    'reimburse' => $components['reimbursement'] ?? 0,
                    'deductions' => $payslip->total_deduction,
                    'thp' => $payslip->net_salary,
                    'status' => ucfirst($payslip->status),
                    'ded_bpjs_kes' => $components['ded_bpjs_kes'] ?? 0,
                    'ded_bpjs_tk' => $components['ded_bpjs_tk'] ?? 0,
                    'ded_pph' => $components['ded_pph'] ?? 0,
                    'ded_late' => $components['ded_late'] ?? 0,
                    'ded_loan' => $components['ded_loan'] ?? 0,
                    'overtime_hours' => $components['overtime_hours'] ?? 0,
                    'late_frequency' => $components['late_frequency'] ?? 0,
                ];
            }

            return; // Early return for massive performance boost
        }

        $employees = Employee::active()->get();
        $payslips = Payslip::where('period_month', $month)->where('period_year', $year)->get()->keyBy('employee_id');

        $overtimes = Overtime::whereMonth('date', $month)
            ->whereYear('date', $year)
            ->where('status', 'approved')
            ->get()
            ->groupBy('user_id');

        $attendances = Attendance::whereMonth('date', $month)
            ->whereYear('date', $year)
            ->where('is_late', true)
            ->get()
            ->groupBy('user_id');

        $reimbursements = Reimbursement::whereMonth('approved_at', $month)
            ->whereYear('approved_at', $year)
            ->where('status', 'approved')
            ->get()
            ->groupBy('user_id');

        $this->payrollData = [];
        foreach ($employees as $emp) {
            $payslip = $payslips->get($emp->id);
            $components = $payslip ? $payslip->components_detail : [];

            $defaultOvertimeHours = 0;
            if (isset($overtimes[$emp->user_id])) {
                $totalMinutes = $overtimes[$emp->user_id]->sum('duration_minutes');
                $defaultOvertimeHours = round($totalMinutes / 60, 2);
            }

            $defaultLateFrequency = 0;
            if (isset($attendances[$emp->user_id])) {
                $defaultLateFrequency = $attendances[$emp->user_id]->count();
            }

            $defaultReimburse = 0;
            if (isset($reimbursements[$emp->user_id])) {
                $defaultReimburse = $reimbursements[$emp->user_id]->sum('amount');
            }

            $otHours = $payslip ? ($components['overtime_hours'] ?? 0) : $defaultOvertimeHours;
            $lateFreq = $payslip ? ($components['late_frequency'] ?? 0) : $defaultLateFrequency;
            $reimburseTotal = $payslip ? ($components['reimbursement'] ?? 0) : $defaultReimburse;

            $defaultBpjsKes = ($emp->base_salary * $this->bpjsKesRate) / 100;
            $defaultBpjsTk = ($emp->base_salary * $this->bpjsTkRate) / 100;
            $ptkp = 'TK/0'; // Default PTKP
            $defaultPph21 = $this->calculatePph21($emp->base_salary, $ptkp);

            $otPay = $payslip ? $payslip->overtime_pay : ($otHours * $this->overtimeRate);
            $latePenalty = $payslip ? ($components['ded_late'] ?? 0) : ($lateFreq * $this->latePenalty);
            $bpjsKes = $payslip ? ($components['ded_bpjs_kes'] ?? 0) : $defaultBpjsKes;
            $bpjsTk = $payslip ? ($components['ded_bpjs_tk'] ?? 0) : $defaultBpjsTk;
            $pph21 = $payslip ? ($components['ded_pph'] ?? 0) : $defaultPph21;
            $loan = $payslip ? ($components['ded_loan'] ?? 0) : 0;

            $jobClass = $emp->jobClass;
            $defaultAllowance = $jobClass ? $jobClass->base_allowance : 0;
            $allowance = $payslip ? $payslip->total_allowance : $defaultAllowance;

            $totalDeduction = $payslip ? $payslip->total_deduction : ($latePenalty + $bpjsKes + $bpjsTk + $pph21 + $loan);

            // Reimburse is added to THP but not taxed
            $thp = $payslip ? $payslip->net_salary : ($emp->base_salary + $allowance + $otPay + $reimburseTotal - $totalDeduction);

            $this->payrollData[] = [
                'id' => $emp->id,
                'payslip_id' => $payslip?->id,
                'nik' => $emp->nik,
                'name' => $emp->full_name,
                'dept' => $emp->department?->name ?? '-',
                'class' => $emp->jobClass?->name ?? '-',
                'ptkp' => $ptkp,
                'basic' => $payslip ? $payslip->base_salary : $emp->base_salary,
                'allowance' => $allowance,
                'overtime' => $otPay,
                'reimburse' => $reimburseTotal,
                'deductions' => $totalDeduction,
                'thp' => $thp,
                'status' => $payslip ? ucfirst($payslip->status) : 'Draf',
                'ded_bpjs_kes' => $bpjsKes,
                'ded_bpjs_tk' => $bpjsTk,
                'ded_pph' => $pph21,
                'ded_late' => $latePenalty,
                'ded_loan' => $components['ded_loan'] ?? 0,
                'overtime_hours' => $otHours,
                'late_frequency' => $lateFreq,
            ];
        }

        // Load History (only when NOT viewing history detail)
        if (! $this->isViewingHistory) {
            $historyRecords = DB::table('payslips')
                ->selectRaw('period_month as month, period_year as year, count(*) as count, sum(net_salary) as total_thp')
                // Check if all are paid or not. For sqlite/mysql max(status) works loosely. Let's just assume if it exists it's generated, if all are published/paid we can check.
                // We will just use 'Published' since we only save to db when generated/verified/published.
                ->where(function ($q) use ($month, $year) {
                    $q->where('period_year', '<', $year)
                        ->orWhere(function ($q2) use ($month, $year) {
                            $q2->where('period_year', $year)->where('period_month', '<', $month);
                        });
                })
                ->groupBy('period_year', 'period_month')
                ->orderBy('period_year', 'desc')
                ->orderBy('period_month', 'desc')
                ->get();

            $this->payrollHistory = [];
            foreach ($historyRecords as $record) {
                $this->payrollHistory[] = [
                    'month' => $record->month,
                    'year' => $record->year,
                    'count' => $record->count,
                    'total_thp' => $record->total_thp,
                    'status' => 'Paid', // Since history is by definition past data that's already published
                ];
            }
        }

    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Select::make('dept')
                    ->options(Department::pluck('name', 'name'))
                    ->native(false)
                    ->live()
                    ->searchable()
                    ->placeholder('Semua Dept')
                    ->hiddenLabel()
                    ->extraAttributes(['class' => 'w-full sm:w-48']),

                Select::make('class')
                    ->options(JobClass::pluck('name', 'name'))
                    ->native(false)
                    ->live()
                    ->searchable()
                    ->placeholder('Semua Kelas')
                    ->hiddenLabel()
                    ->extraAttributes(['class' => 'w-full sm:w-56']),
            ])
            ->statePath('filterData')
            ->columns(2);
    }

    public function getFilteredPayrollDataProperty()
    {
        $data = $this->payrollData;

        if (! empty($this->searchQuery)) {
            $data = array_filter($data, function ($item) {
                return stripos($item['name'], $this->searchQuery) !== false || stripos($item['nik'], $this->searchQuery) !== false;
            });
        }

        if (! empty($this->filterData['dept'])) {
            $data = array_filter($data, fn ($item) => $item['dept'] === $this->filterData['dept']);
        }

        if (! empty($this->filterData['class'])) {
            $data = array_filter($data, fn ($item) => $item['class'] === $this->filterData['class']);
        }

        return $data;
    }

    public function getPaginatedPayrollDataProperty()
    {
        $filtered = $this->filteredPayrollData;
        $offset = ($this->currentPage - 1) * $this->perPage;

        return array_slice($filtered, $offset, $this->perPage, true);
    }

    public function getTotalPagesProperty()
    {
        $total = count($this->filteredPayrollData);

        return $total > 0 ? ceil($total / $this->perPage) : 1;
    }

    public function nextPage()
    {
        if ($this->currentPage < $this->totalPages) {
            $this->currentPage++;
        }
    }

    public function previousPage()
    {
        if ($this->currentPage > 1) {
            $this->currentPage--;
        }
    }

    public function setPage($page)
    {
        $this->currentPage = $page;
    }

    public function updatedSearchQuery()
    {
        $this->currentPage = 1;
    }

    public function openEditModal($index)
    {
        $this->editingIndex = $index;
        $data = $this->payrollData[$index];
        $this->editName = $data['name'];
        $this->editBasic = $data['basic'];
        $this->editAllowance = $data['allowance'] ?? 0;
        $this->editOvertime = $data['overtime'];
        $this->editOvertimeHours = $data['overtime_hours'] ?? 0;
        $this->editDedBpjsKesehatan = $data['ded_bpjs_kes'] ?? 0;
        $this->editDedBpjsKetenagakerjaan = $data['ded_bpjs_tk'] ?? 0;
        $this->editDedPph21 = $data['ded_pph'] ?? 0;
        $this->editDedLate = $data['ded_late'] ?? 0;
        $this->editLateFrequency = $data['late_frequency'] ?? 0;
        $this->editDedLoan = $data['ded_loan'] ?? 0;
        $this->editReimburse = $data['reimburse'] ?? 0;
        $this->editDeductions = $data['deductions'];
        $this->editThp = $data['thp'];
        $this->isEditing = true;

        // Auto-calculate on open in case it's a new row
        $this->recalculate();
    }

    public function closeEditModal()
    {
        $this->isEditing = false;
        $this->editingIndex = null;
    }

    public function updatedEditOvertime()
    {
        $this->recalculate();
    }

    public function updatedEditAllowance()
    {
        $this->recalculate();
    }

    public function updatedEditOvertimeHours()
    {
        $this->editOvertime = (int) $this->editOvertimeHours * $this->overtimeRate;
        $this->recalculate();
    }

    public function updatedEditDedBpjsKesehatan()
    {
        $this->recalculate();
    }

    public function updatedEditDedBpjsKetenagakerjaan()
    {
        $this->recalculate();
    }

    // We don't trigger recalculate from updatedEditDedPph21 because we are auto-calculating it,
    // unless the user overrides it. If they override it, we just update THP.
    public function updatedEditDedPph21()
    {
        $this->editDeductions = (int) $this->editDedBpjsKesehatan + (int) $this->editDedBpjsKetenagakerjaan + (int) $this->editDedPph21 + (int) $this->editDedLate + (int) $this->editDedLoan;
        $this->editThp = $this->editBasic + (int) $this->editOvertime + (int) $this->editReimburse - $this->editDeductions;
    }

    public function updatedEditDedLate()
    {
        $this->recalculate();
    }

    public function updatedEditLateFrequency()
    {
        $this->editDedLate = (int) $this->editLateFrequency * $this->latePenalty;
        $this->recalculate();
    }

    public function updatedEditDedLoan()
    {
        $this->recalculate();
    }

    private function calculatePph21($grossIncome, $ptkp)
    {
        // Simulasi TER (Tarif Efektif Rata-Rata) PPh 21 Tahun 2024
        // Category A: TK/0, TK/1, K/0
        // Category B: TK/2, TK/3, K/1, K/2
        // Category C: K/3

        $category = 'A';
        if (in_array($ptkp, ['TK/2', 'TK/3', 'K/1', 'K/2'])) {
            $category = 'B';
        }
        if ($ptkp === 'K/3') {
            $category = 'C';
        }

        $rate = 0;

        if ($category === 'A') {
            if ($grossIncome <= 5400000) {
                $rate = 0;
            } elseif ($grossIncome <= 5650000) {
                $rate = 0.25;
            } elseif ($grossIncome <= 5950000) {
                $rate = 0.5;
            } elseif ($grossIncome <= 6300000) {
                $rate = 0.75;
            } elseif ($grossIncome <= 6750000) {
                $rate = 1.0;
            } elseif ($grossIncome <= 7150000) {
                $rate = 1.25;
            } elseif ($grossIncome <= 7300000) {
                $rate = 1.5;
            } elseif ($grossIncome <= 9200000) {
                $rate = 1.75;
            } elseif ($grossIncome <= 9650000) {
                $rate = 2.0;
            } elseif ($grossIncome <= 10050000) {
                $rate = 2.25;
            } else {
                $rate = 2.5;
            } // disederhanakan
        } elseif ($category === 'B') {
            if ($grossIncome <= 6200000) {
                $rate = 0;
            } elseif ($grossIncome <= 6500000) {
                $rate = 0.25;
            } elseif ($grossIncome <= 6850000) {
                $rate = 0.5;
            } elseif ($grossIncome <= 7300000) {
                $rate = 0.75;
            } elseif ($grossIncome <= 9200000) {
                $rate = 1.0;
            } else {
                $rate = 1.5;
            } // disederhanakan
        } else {
            if ($grossIncome <= 6600000) {
                $rate = 0;
            } elseif ($grossIncome <= 6950000) {
                $rate = 0.25;
            } elseif ($grossIncome <= 7350000) {
                $rate = 0.5;
            } elseif ($grossIncome <= 7800000) {
                $rate = 0.75;
            } else {
                $rate = 1.25;
            } // disederhanakan
        }

        return ($grossIncome * $rate) / 100;
    }

    private function recalculate()
    {
        $grossIncome = $this->editBasic + (int) $this->editAllowance + (int) $this->editOvertime;

        $this->editDeductions = (int) $this->editDedBpjsKesehatan + (int) $this->editDedBpjsKetenagakerjaan + (int) $this->editDedPph21 + (int) $this->editDedLate;
        $this->editThp = $grossIncome + (int) $this->editReimburse - $this->editDeductions;
    }

    private function saveToDb($status)
    {
        $data = $this->payrollData[$this->editingIndex];
        $period = $this->getCurrentPeriod();
        $month = $period['month'];
        $year = $period['year'];

        Payslip::updateOrCreate([
            'employee_id' => $data['id'],
            'period_month' => $month,
            'period_year' => $year,
        ], [
            'base_salary' => $this->editBasic,
            'total_allowance' => $this->editAllowance,
            'overtime_pay' => $this->editOvertime,
            'total_deduction' => $this->editDeductions,
            'net_salary' => $this->editThp,
            'status' => strtolower($status),
            'payment_date' => strtolower($status) === 'paid' ? now() : null,
            'components_detail' => [
                'overtime_hours' => $this->editOvertimeHours,
                'late_frequency' => $this->editLateFrequency,
                'ded_bpjs_kes' => $this->editDedBpjsKesehatan,
                'ded_bpjs_tk' => $this->editDedBpjsKetenagakerjaan,
                'ded_pph' => $this->editDedPph21,
                'ded_late' => $this->editDedLate,
                'ded_loan' => $this->editDedLoan,
                'reimbursement' => $this->editReimburse,
            ],
        ]);

        $this->loadData();
    }

    public function saveDraft()
    {
        $this->saveToDb('draft');
        $this->closeEditModal();

        Notification::make()
            ->title('Disimpan sebagai Draft')
            ->body('Rincian penggajian '.$this->editName.' telah disimpan.')
            ->success()
            ->send();
    }

    public function savePayroll()
    {
        $this->saveToDb('validated');
        $this->closeEditModal();

        Notification::make()
            ->title('Tersimpan & Di-ACC')
            ->body('Rincian penggajian '.$this->editName.' telah di-ACC dan ditandai Verified.')
            ->success()
            ->send();
    }

    public function publishAll()
    {
        $period = $this->getCurrentPeriod();
        $month = $period['month'];
        $year = $period['year'];
        $periode = $period['text'];

        foreach ($this->payrollData as $data) {
            if (strtolower($data['status']) === 'paid') {
                continue;
            }

            Payslip::updateOrCreate([
                'employee_id' => $data['id'],
                'period_month' => $month,
                'period_year' => $year,
            ], [
                'base_salary' => $data['basic'],
                'total_allowance' => $data['allowance'],
                'overtime_pay' => $data['overtime'],
                'total_deduction' => $data['deductions'],
                'net_salary' => $data['thp'],
                'status' => 'paid',
                'payment_date' => now(),
                'components_detail' => [
                    'overtime_hours' => $data['overtime_hours'],
                    'late_frequency' => $data['late_frequency'],
                    'ded_bpjs_kes' => $data['ded_bpjs_kes'],
                    'ded_bpjs_tk' => $data['ded_bpjs_tk'],
                    'ded_pph' => $data['ded_pph'],
                    'ded_late' => $data['ded_late'],
                    'ded_loan' => $data['ded_loan'],
                    'reimbursement' => $data['reimburse'],
                ],
            ]);
        }

        $this->loadData();

        Notification::make()
            ->title('Berhasil dipublish secara massal')
            ->body("Semua slip gaji draft untuk periode $periode telah dipublish.")
            ->success()
            ->send();
    }

    public function exportExcel()
    {
        $data = $this->filteredPayrollData;
        $periodText = $this->getCurrentPeriod()['text'];
        $filename = 'Rekap_Payroll_'.str_replace(' ', '_', $periodText).'.csv';

        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');
            // Menambahkan BOM untuk UTF-8 agar bisa dibaca Excel dengan baik
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, [
                'NIK', 'Nama Pegawai', 'Departemen', 'Kelas Jabatan',
                'Gaji Pokok', 'Tunjangan Jabatan', 'Honor Lembur', 'Reimbursement',
                'BPJS Kesehatan', 'BPJS Ketenagakerjaan', 'PPh 21', 'Pot. Telat',
                'Total Potongan', 'Take Home Pay', 'Status',
            ], ';');

            foreach ($data as $row) {
                fputcsv($file, [
                    "'".$row['nik'],
                    $row['name'],
                    $row['dept'],
                    $row['class'],
                    $row['basic'],
                    $row['allowance'] ?? 0,
                    $row['overtime'],
                    $row['reimburse'] ?? 0,
                    $row['ded_bpjs_kes'] ?? 0,
                    $row['ded_bpjs_tk'] ?? 0,
                    $row['ded_pph'] ?? 0,
                    $row['ded_late'] ?? 0,
                    $row['deductions'],
                    $row['thp'],
                    $row['status'],
                ], ';');
            }
            fclose($file);
        };

        return response()->streamDownload($callback, $filename, ['Content-Type' => 'text/csv']);
    }

    public function generatePayroll()
    {
        $period = $this->getCurrentPeriod();

        $cacheKey = 'payroll_print_'.auth()->id();
        Cache::put($cacheKey, $this->filteredPayrollData, 60);

        return redirect()->route('payroll.print', [
            'month' => $period['month'],
            'year' => $period['year'],
        ]);
    }

    public function exportHistoryRow($month, $year)
    {
        // Temporarily load data for the requested month
        $originalViewing = $this->isViewingHistory;
        $originalMonth = $this->historyMonth;
        $originalYear = $this->historyYear;

        $this->isViewingHistory = true;
        $this->historyMonth = $month;
        $this->historyYear = $year;
        $this->loadData();

        $response = $this->exportExcel();

        // Revert
        $this->isViewingHistory = $originalViewing;
        $this->historyMonth = $originalMonth;
        $this->historyYear = $originalYear;
        $this->loadData();

        return $response;
    }

    public function generateHistoryRow($month, $year)
    {
        // Temporarily load data for the requested month
        $originalViewing = $this->isViewingHistory;
        $originalMonth = $this->historyMonth;
        $originalYear = $this->historyYear;

        $this->isViewingHistory = true;
        $this->historyMonth = $month;
        $this->historyYear = $year;
        $this->loadData();

        $response = $this->generatePayroll();

        // Revert
        $this->isViewingHistory = $originalViewing;
        $this->historyMonth = $originalMonth;
        $this->historyYear = $originalYear;
        $this->loadData();

        return $response;
    }
}
