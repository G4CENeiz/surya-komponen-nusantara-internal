<?php

namespace App\Filament\Accounting\Pages;

use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;

class Payroll extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-currency-dollar';

    protected static ?string $navigationLabel = 'Payroll';

    protected static ?string $title = 'Kelola Penggajian';

    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.accounting.pages.payroll';

    public array $payrollData = [];

    public $isEditing = false;

    public $editingIndex = null;

    public $editName = '';

    public ?array $filterData = [];

    public $editBasic = 0;

    public $editOvertime = 0;

    public $editDedBpjsKesehatan = 0;

    public $editDedBpjsKetenagakerjaan = 0;

    public $editDedPph21 = 0;

    public $editDedLate = 0;

    public $editDedLoan = 0;

    public $editDeductions = 0;

    public $editThp = 0;

    public function mount()
    {
        $this->payrollData = [
            ['nik' => 'EMP-001', 'name' => 'Budi Santoso', 'ptkp' => 'TK/0', 'basic' => 4000000, 'overtime' => 250000, 'deductions' => 120000, 'thp' => 4130000, 'status' => 'Draft', 'ded_bpjs_kes' => 40000, 'ded_bpjs_tk' => 80000, 'ded_pph' => 0, 'ded_late' => 0, 'ded_loan' => 0],
            ['nik' => 'EMP-002', 'name' => 'Siti Aminah', 'ptkp' => 'K/1', 'basic' => 4500000, 'overtime' => 500000, 'deductions' => 135000, 'thp' => 4865000, 'status' => 'Draft', 'ded_bpjs_kes' => 45000, 'ded_bpjs_tk' => 90000, 'ded_pph' => 0, 'ded_late' => 0, 'ded_loan' => 0],
            ['nik' => 'EMP-003', 'name' => 'Agus Pratama', 'ptkp' => 'TK/0', 'basic' => 4200000, 'overtime' => 0, 'deductions' => 176000, 'thp' => 4024000, 'status' => 'Published', 'ded_bpjs_kes' => 42000, 'ded_bpjs_tk' => 84000, 'ded_pph' => 0, 'ded_late' => 50000, 'ded_loan' => 0],
            ['nik' => 'EMP-004', 'name' => 'Rina Wijaya', 'ptkp' => 'TK/0', 'basic' => 5000000, 'overtime' => 125000, 'deductions' => 150000, 'thp' => 4975000, 'status' => 'Draft', 'ded_bpjs_kes' => 50000, 'ded_bpjs_tk' => 100000, 'ded_pph' => 0, 'ded_late' => 0, 'ded_loan' => 0],
            ['nik' => 'EMP-005', 'name' => 'Ahmad Fauzi', 'ptkp' => 'TK/0', 'basic' => 7000000, 'overtime' => 500000, 'deductions' => 0, 'thp' => 0, 'status' => 'Draft', 'ded_bpjs_kes' => 70000, 'ded_bpjs_tk' => 140000, 'ded_pph' => 0, 'ded_late' => 0, 'ded_loan' => 0],
        ];

        $this->form->fill();
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Select::make('periode')
                    ->options([
                        'Juni 2026' => 'Juni 2026',
                        'Mei 2026' => 'Mei 2026',
                        'April 2026' => 'April 2026',
                        'Maret 2026' => 'Maret 2026',
                    ])
                    ->native(false)
                    ->live()
                    ->default('Juni 2026')
                    ->selectablePlaceholder(false)
                    ->hiddenLabel()
                    ->extraAttributes(['class' => 'w-full sm:w-48']),
            ])
            ->statePath('filterData');
    }

    public function openEditModal($index)
    {
        $this->editingIndex = $index;
        $data = $this->payrollData[$index];
        $this->editName = $data['name'];
        $this->editBasic = $data['basic'];
        $this->editOvertime = $data['overtime'];
        $this->editDedBpjsKesehatan = $data['ded_bpjs_kes'] ?? 0;
        $this->editDedBpjsKetenagakerjaan = $data['ded_bpjs_tk'] ?? 0;
        $this->editDedPph21 = $data['ded_pph'] ?? 0;
        $this->editDedLate = $data['ded_late'] ?? 0;
        $this->editDedLoan = $data['ded_loan'] ?? 0;
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
        $this->editThp = $this->editBasic + (int) $this->editOvertime - $this->editDeductions;
    }

    public function updatedEditDedLate()
    {
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
        $grossIncome = $this->editBasic + (int) $this->editOvertime;
        $ptkp = $this->payrollData[$this->editingIndex]['ptkp'] ?? 'TK/0';

        // Sesuai permintaan/kebijakan perusahaan, hitung pajak hanya dari Gaji Pokok
        $taxableIncome = $this->editBasic;

        // Auto hitung PPh 21
        $this->editDedPph21 = $this->calculatePph21($taxableIncome, $ptkp);

        $this->editDeductions = (int) $this->editDedBpjsKesehatan + (int) $this->editDedBpjsKetenagakerjaan + (int) $this->editDedPph21 + (int) $this->editDedLate + (int) $this->editDedLoan;
        $this->editThp = $grossIncome - $this->editDeductions;
    }

    public function savePayroll()
    {
        $this->payrollData[$this->editingIndex]['overtime'] = (int) $this->editOvertime;
        $this->payrollData[$this->editingIndex]['ded_bpjs_kes'] = (int) $this->editDedBpjsKesehatan;
        $this->payrollData[$this->editingIndex]['ded_bpjs_tk'] = (int) $this->editDedBpjsKetenagakerjaan;
        $this->payrollData[$this->editingIndex]['ded_pph'] = (int) $this->editDedPph21;
        $this->payrollData[$this->editingIndex]['ded_late'] = (int) $this->editDedLate;
        $this->payrollData[$this->editingIndex]['ded_loan'] = (int) $this->editDedLoan;
        $this->payrollData[$this->editingIndex]['deductions'] = $this->editDeductions;
        $this->payrollData[$this->editingIndex]['thp'] = $this->editThp;
        $this->closeEditModal();

        Notification::make()
            ->title('Berhasil diperbarui')
            ->body('Rincian penggajian '.$this->editName.' telah disimpan sebagai Draft.')
            ->success()
            ->send();
    }

    public function publishPayroll()
    {
        $this->payrollData[$this->editingIndex]['overtime'] = (int) $this->editOvertime;
        $this->payrollData[$this->editingIndex]['ded_bpjs_kes'] = (int) $this->editDedBpjsKesehatan;
        $this->payrollData[$this->editingIndex]['ded_bpjs_tk'] = (int) $this->editDedBpjsKetenagakerjaan;
        $this->payrollData[$this->editingIndex]['ded_pph'] = (int) $this->editDedPph21;
        $this->payrollData[$this->editingIndex]['ded_late'] = (int) $this->editDedLate;
        $this->payrollData[$this->editingIndex]['ded_loan'] = (int) $this->editDedLoan;
        $this->payrollData[$this->editingIndex]['deductions'] = $this->editDeductions;
        $this->payrollData[$this->editingIndex]['thp'] = $this->editThp;
        $this->payrollData[$this->editingIndex]['status'] = 'Published'; // Mark as published
        $this->closeEditModal();

        Notification::make()
            ->title('Berhasil dipublish')
            ->body('Rincian penggajian '.$this->editName.' telah dipublish dan dikunci.')
            ->success()
            ->send();
    }
}
