<?php

namespace App\Filament\Employee\Pages;

use App\Models\Payroll;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Placeholder;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MySalary extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationLabel = 'Komponen Gaji';

    protected static ?string $title = 'Komponen Gaji Saya';

    protected string $view = 'filament.employee.pages.my-salary';

    public function content(Schema $schema): Schema
    {
        $user = auth()->user();
        $jobClass = $user?->jobClass;
        $latestPayroll = Payroll::where('user_id', $user?->id)
            ->latest('created_at')
            ->first();

        return $schema->components([
            Grid::make(2)
                ->schema([
                    Section::make('Kelas Jabatan')
                        ->schema([
                            Placeholder::make('job_class_name')
                                ->label('Jabatan')
                                ->content($jobClass?->name ?? '—'),
                            Placeholder::make('base_salary')
                                ->label('Gaji Pokok')
                                ->content($jobClass ? 'Rp '.number_format($jobClass->base_salary, 0, ',', '.') : '—'),
                            Placeholder::make('allowance')
                                ->label('Tunjangan')
                                ->content($jobClass ? 'Rp '.number_format($jobClass->allowance, 0, ',', '.') : '—'),
                            Placeholder::make('overtime_rate')
                                ->label('Tarif Lembur/Jam')
                                ->content($jobClass ? 'Rp '.number_format($jobClass->base_salary / 173 * 1.5, 0, ',', '.') : '—'),
                        ]),

                    Section::make('Slip Gaji Terakhir')
                        ->schema([
                            Placeholder::make('period')
                                ->label('Periode')
                                ->content($latestPayroll?->payrollPeriod?->name ?? 'Belum ada slip gaji'),
                            Placeholder::make('lp_base_salary')
                                ->label('Gaji Pokok')
                                ->content($latestPayroll ? 'Rp '.number_format($latestPayroll->base_salary, 0, ',', '.') : '—'),
                            Placeholder::make('lp_overtime')
                                ->label('Honor Lembur')
                                ->content($latestPayroll ? 'Rp '.number_format($latestPayroll->overtime_pay, 0, ',', '.') : '—'),
                            Placeholder::make('lp_deductions')
                                ->label('Total Potongan')
                                ->content($latestPayroll ? 'Rp '.number_format(
                                    $latestPayroll->bpjs_health + $latestPayroll->bpjs_employment + $latestPayroll->pph21 + $latestPayroll->tardiness_deduction + $latestPayroll->other_deductions,
                                    0, ',', '.'
                                ) : '—'),
                            Placeholder::make('lp_thp')
                                ->label('Take-Home Pay')
                                ->content($latestPayroll ? 'Rp '.number_format($latestPayroll->take_home_pay, 0, ',', '.') : '—')
                                ->extraAttributes(['class' => 'text-xl font-bold text-success-600']),
                        ]),
                ]),
        ]);
    }
}
