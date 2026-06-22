<?php

namespace App\Filament\Accounting\Resources;

use App\Filament\Accounting\Resources\PayrollResource\Pages;
use App\Models\Payroll;
use BackedEnum;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Placeholder;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;

class PayrollResource extends Resource
{
    protected static ?string $model = Payroll::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationLabel = 'Slip Gaji';

    protected static string|UnitEnum|null $navigationGroup = 'Penggajian';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Pegawai')
                    ->schema([
                        Placeholder::make('employee_name')
                            ->label('Nama')
                            ->content(fn (Payroll $record): string => $record->user?->name ?? '—'),
                        Placeholder::make('period_name')
                            ->label('Periode')
                            ->content(fn (Payroll $record): string => $record->payrollPeriod?->name ?? '—'),
                        Placeholder::make('job_class')
                            ->label('Jabatan')
                            ->content(fn (Payroll $record): string => $record->user?->jobClass?->name ?? '—'),
                    ])->columns(3),

                Section::make('Pendapatan')
                    ->schema([
                        Placeholder::make('base_salary_display')
                            ->label('Gaji Pokok')
                            ->content(fn (Payroll $record): string => 'Rp '.number_format($record->base_salary, 0, ',', '.')),
                        Placeholder::make('allowance_display')
                            ->label('Tunjangan')
                            ->content(fn (Payroll $record): string => 'Rp '.number_format($record->allowance, 0, ',', '.')),
                        Placeholder::make('overtime_display')
                            ->label('Honor Lembur')
                            ->content(fn (Payroll $record): string => 'Rp '.number_format($record->overtime_pay, 0, ',', '.')),
                    ])->columns(3),

                Section::make('Potongan')
                    ->schema([
                        Placeholder::make('bpjs_health_display')
                            ->label('BPJS Kesehatan')
                            ->content(fn (Payroll $record): string => 'Rp '.number_format($record->bpjs_health, 0, ',', '.')),
                        Placeholder::make('bpjs_employment_display')
                            ->label('BPJS Ketenagakerjaan')
                            ->content(fn (Payroll $record): string => 'Rp '.number_format($record->bpjs_employment, 0, ',', '.')),
                        Placeholder::make('pph21_display')
                            ->label('PPh 21')
                            ->content(fn (Payroll $record): string => 'Rp '.number_format($record->pph21, 0, ',', '.')),
                        Placeholder::make('tardiness_display')
                            ->label('Potongan Keterlambatan')
                            ->content(fn (Payroll $record): string => 'Rp '.number_format($record->tardiness_deduction, 0, ',', '.')),
                        Placeholder::make('other_deductions_display')
                            ->label('Potongan Lainnya')
                            ->content(fn (Payroll $record): string => 'Rp '.number_format($record->other_deductions, 0, ',', '.')),
                    ])->columns(3),

                Section::make('Take-Home Pay')
                    ->schema([
                        Placeholder::make('thp')
                            ->label('Total Take-Home Pay')
                            ->content(fn (Payroll $record): string => 'Rp '.number_format($record->take_home_pay, 0, ',', '.'))
                            ->extraAttributes(['class' => 'text-2xl font-bold text-success-600']),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Pegawai')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.jobClass.name')
                    ->label('Jabatan')
                    ->sortable(),
                Tables\Columns\TextColumn::make('base_salary')
                    ->label('Gaji Pokok')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('overtime_pay')
                    ->label('Lembur')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('tardiness_deduction')
                    ->label('Potongan')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('take_home_pay')
                    ->label('THP')
                    ->money('IDR')
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => 'Draft',
                        'finalized' => 'Finalized',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'warning',
                        'finalized' => 'success',
                        default => 'gray',
                    }),
            ])
            ->defaultSort('user.name', 'asc')
            ->actions([
                ViewAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayrolls::route('/'),
            'view' => Pages\ViewPayroll::route('/{record}'),
        ];
    }
}
