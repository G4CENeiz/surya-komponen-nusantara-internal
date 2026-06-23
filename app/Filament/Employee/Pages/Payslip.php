<?php

namespace App\Filament\Employee\Pages;

use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas\Schema;

class Payslip extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationLabel = 'Slip Gaji';

    protected static ?int $navigationSort = 4;

    protected static ?string $title = 'Slip Gaji & Riwayat';

    protected string $view = 'filament.employee.pages.payslip';

    public array $gajiList = [];

    public ?array $data = [];

    public array $gaji = [];

    public function mount(): void
    {
        $user = auth()->user();
        $employee = $user?->employee;
        $months = [1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'];

        $payslips = \App\Models\Payslip::where('employee_id', $employee?->id)
            ->where('status', 'paid') // Only show paid ones
            ->orderBy('period_year', 'desc')
            ->orderBy('period_month', 'desc')
            ->get();

        $this->gajiList = [];
        $options = [];

        foreach ($payslips as $ps) {
            $period = $months[$ps->period_month].' '.$ps->period_year;
            $options[$period] = $period;

            $components = $ps->components_detail ?? [];

            // Remove 0 values for cleaner UI
            $potongan = [];
            if (($components['ded_bpjs_kes'] ?? 0) > 0) {
                $potongan['BPJS Kesehatan'] = $components['ded_bpjs_kes'];
            }
            if (($components['ded_bpjs_tk'] ?? 0) > 0) {
                $potongan['BPJS Ketenagakerjaan'] = $components['ded_bpjs_tk'];
            }
            if (($components['ded_pph'] ?? 0) > 0) {
                $potongan['PPh 21'] = $components['ded_pph'];
            }

            $lateLabel = 'Keterlambatan';
            if (($components['late_frequency'] ?? 0) > 0) {
                $lateLabel .= ' ('.$components['late_frequency'].'x)';
            }
            if (($components['ded_late'] ?? 0) > 0) {
                $potongan[$lateLabel] = $components['ded_late'];
            }
            if (($components['ded_loan'] ?? 0) > 0) {
                $potongan['Pinjaman / Lainnya'] = $components['ded_loan'];
            }

            $pendapatan = ['Gaji Pokok' => $ps->base_salary];

            $otLabel = 'Honor Lembur';
            if (($components['overtime_hours'] ?? 0) > 0) {
                $otLabel .= ' ('.$components['overtime_hours'].' Jam)';
            }
            if ($ps->overtime_pay > 0) {
                $pendapatan[$otLabel] = $ps->overtime_pay;
            }

            $this->gajiList[$period] = [
                'periode' => $period,
                'kelas_jabatan' => $employee?->jobClass?->name ?? '-',
                'pendapatan' => $pendapatan,
                'potongan' => $potongan,
                'total_pendapatan' => $ps->base_salary + $ps->overtime_pay,
                'total_potongan' => $ps->total_deduction,
                'take_home_pay' => $ps->net_salary,
            ];
        }

        if (empty($options)) {
            $options['Belum Ada Data'] = 'Belum Ada Data';
            $this->gajiList['Belum Ada Data'] = [
                'periode' => 'Belum Ada Data',
                'kelas_jabatan' => $employee?->jobClass?->name ?? '-',
                'pendapatan' => [],
                'potongan' => [],
                'total_pendapatan' => 0,
                'total_potongan' => 0,
                'take_home_pay' => 0,
            ];
        }

        $this->form->fill([
            'selectedPeriode' => array_key_first($options),
        ]);

        $this->updateGaji();
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Select::make('selectedPeriode')
                    ->hiddenLabel()
                    ->options(array_combine(array_keys($this->gajiList), array_keys($this->gajiList)))
                    ->live()
                    ->native(false)
                    ->afterStateUpdated(fn () => $this->updateGaji())
                    ->extraAttributes(['class' => 'w-48'])
                    ->selectablePlaceholder(false),
            ])
            ->statePath('data');
    }

    private function updateGaji()
    {
        $periode = $this->data['selectedPeriode'] ?? array_key_first($this->gajiList);
        $this->gaji = $this->gajiList[$periode] ?? [];
    }
}
