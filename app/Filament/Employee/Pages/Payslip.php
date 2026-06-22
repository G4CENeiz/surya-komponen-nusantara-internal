<?php

namespace App\Filament\Employee\Pages;

use Filament\Pages\Page;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;

class Payslip extends Page implements HasForms
{
    use InteractsWithForms;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationLabel = 'Slip Gaji';
    
    protected static ?string $title = 'Slip Gaji & Riwayat';

    protected string $view = 'filament.employee.pages.payslip';

    public array $gajiList = [];
    public ?array $data = [];
    public array $gaji = [];

    public function mount()
    {
        $this->gajiList = [
            'Juni 2026' => [
                'periode' => 'Juni 2026',
                'kelas_jabatan' => 'Operator Produksi - Golongan II',
                'pendapatan' => [
                    'Gaji Pokok' => 4500000,
                    'Honor Lembur (15 Jam)' => 450000,
                ],
                'potongan' => [
                    'BPJS Kesehatan' => 45000,
                    'BPJS Ketenagakerjaan' => 90000,
                    'Keterlambatan (2x)' => 50000,
                ],
                'total_pendapatan' => 4950000,
                'total_potongan' => 185000,
                'take_home_pay' => 4765000,
            ],
            'Mei 2026' => [
                'periode' => 'Mei 2026',
                'kelas_jabatan' => 'Operator Produksi - Golongan II',
                'pendapatan' => [
                    'Gaji Pokok' => 4500000,
                    'Honor Lembur (8 Jam)' => 240000,
                ],
                'potongan' => [
                    'BPJS Kesehatan' => 45000,
                    'BPJS Ketenagakerjaan' => 90000,
                    'Keterlambatan (0x)' => 0,
                ],
                'total_pendapatan' => 4740000,
                'total_potongan' => 135000,
                'take_home_pay' => 4605000,
            ],
            'April 2026' => [
                'periode' => 'April 2026',
                'kelas_jabatan' => 'Operator Produksi - Golongan II',
                'pendapatan' => [
                    'Gaji Pokok' => 4500000,
                    'Honor Lembur (20 Jam)' => 600000,
                ],
                'potongan' => [
                    'BPJS Kesehatan' => 45000,
                    'BPJS Ketenagakerjaan' => 90000,
                    'Keterlambatan (1x)' => 25000,
                ],
                'total_pendapatan' => 5100000,
                'total_potongan' => 160000,
                'take_home_pay' => 4940000,
            ],
        ];

        $this->form->fill([
            'selectedPeriode' => 'Juni 2026',
        ]);
        
        $this->updateGaji();
    }

    public function form(\Filament\Schemas\Schema $form): \Filament\Schemas\Schema
    {
        return $form
            ->schema([
                \Filament\Forms\Components\Select::make('selectedPeriode')
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
        $periode = $this->data['selectedPeriode'] ?? 'Juni 2026';
        $this->gaji = $this->gajiList[$periode] ?? [];
    }
}
