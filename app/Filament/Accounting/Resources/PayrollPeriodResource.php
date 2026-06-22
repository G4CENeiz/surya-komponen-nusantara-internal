<?php

namespace App\Filament\Accounting\Resources;

use App\Filament\Accounting\Resources\PayrollPeriodResource\Pages;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\Payroll;
use App\Models\PayrollPeriod;
use App\Models\User;
use BackedEnum;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;

class PayrollPeriodResource extends Resource
{
    protected static ?string $model = PayrollPeriod::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-calendar';

    protected static ?string $navigationLabel = 'Periode Gaji';

    protected static string|UnitEnum|null $navigationGroup = 'Penggajian';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nama Periode')
                    ->required()
                    ->placeholder('e.g. Juni 2026'),
                Select::make('month')
                    ->label('Bulan')
                    ->options([
                        1 => 'Januari', 2 => 'Februari', 3 => 'Maret',
                        4 => 'April', 5 => 'Mei', 6 => 'Juni',
                        7 => 'Juli', 8 => 'Agustus', 9 => 'September',
                        10 => 'Oktober', 11 => 'November', 12 => 'Desember',
                    ])
                    ->required(),
                TextInput::make('year')
                    ->label('Tahun')
                    ->numeric()
                    ->required()
                    ->default(now()->year),
                Select::make('status')
                    ->label('Status')
                    ->options([
                        'draft' => 'Draft',
                        'processed' => 'Diproses',
                        'closed' => 'Ditutup',
                    ])
                    ->default('draft')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Periode')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => 'Draft',
                        'processed' => 'Diproses',
                        'closed' => 'Ditutup',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'warning',
                        'processed' => 'success',
                        'closed' => 'gray',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('payrolls_count')
                    ->label('Jumlah Slip')
                    ->counts('payrolls')
                    ->sortable(),
                Tables\Columns\TextColumn::make('processor.name')
                    ->label('Diproses Oleh'),
                Tables\Columns\TextColumn::make('processed_at')
                    ->dateTime('d M Y H:i')
                    ->label('Waktu Proses'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                EditAction::make(),
                Action::make('generatePayrolls')
                    ->label('Generate Slip')
                    ->icon('heroicon-o-calculator')
                    ->color('success')
                    ->visible(fn (PayrollPeriod $record): bool => $record->status === 'draft')
                    ->requiresConfirmation()
                    ->action(function (PayrollPeriod $record): void {
                        $employees = User::whereHas('roles', fn ($q) => $q->where('name', 'employee'))
                            ->whereNotNull('job_class_id')
                            ->get();

                        $count = 0;
                        foreach ($employees as $employee) {
                            $jobClass = $employee->jobClass;
                            if (! $jobClass) {
                                continue;
                            }

                            // Count overtime hours from approved leave requests
                            $overtimeRequests = LeaveRequest::where('user_id', $employee->id)
                                ->where('type', 'overtime')
                                ->where('status', 'approved')
                                ->whereMonth('start_date', $record->month)
                                ->whereYear('start_date', $record->year)
                                ->get();

                            $overtimeHours = 0;
                            foreach ($overtimeRequests as $ot) {
                                if ($ot->start_time && $ot->end_time) {
                                    $start = Carbon::parse($ot->start_time);
                                    $end = Carbon::parse($ot->end_time);
                                    $overtimeHours += $start->diffInHours($end);
                                }
                            }

                            // Count tardy days
                            $tardyDays = Attendance::where('user_id', $employee->id)
                                ->whereMonth('date', $record->month)
                                ->whereYear('date', $record->year)
                                ->where('is_late', true)
                                ->count();

                            $baseSalary = (float) $jobClass->base_salary;
                            $allowance = (float) $jobClass->allowance;
                            $hourlyRate = $baseSalary / 173; // Indonesian standard
                            $overtimePay = $overtimeHours * $hourlyRate * 1.5;
                            $tardinessDeduction = $tardyDays * ($baseSalary / 30) * 0.5; // Half-day deduction per tardy
                            $bpjsHealth = $baseSalary * 0.04; // 4%
                            $bpjsEmployment = $baseSalary * 0.037; // 3.7%
                            $pph21 = $baseSalary > 5000000 ? $baseSalary * 0.05 : 0; // Simplified PPh21
                            $takeHomePay = $baseSalary + $allowance + $overtimePay - $bpjsHealth - $bpjsEmployment - $pph21 - $tardinessDeduction;

                            Payroll::updateOrCreate(
                                ['user_id' => $employee->id, 'payroll_period_id' => $record->id],
                                [
                                    'base_salary' => $baseSalary,
                                    'allowance' => $allowance,
                                    'overtime_hours' => $overtimeHours,
                                    'overtime_pay' => $overtimePay,
                                    'bpjs_health' => $bpjsHealth,
                                    'bpjs_employment' => $bpjsEmployment,
                                    'pph21' => $pph21,
                                    'tardiness_deduction' => $tardinessDeduction,
                                    'other_deductions' => 0,
                                    'take_home_pay' => max(0, $takeHomePay),
                                    'status' => 'draft',
                                ],
                            );
                            $count++;
                        }

                        $record->update(['status' => 'processed', 'processed_by' => auth()->id(), 'processed_at' => now()]);

                        Notification::make()
                            ->title("{$count} slip gaji berhasil dibuat")
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayrollPeriods::route('/'),
            'create' => Pages\CreatePayrollPeriod::route('/create'),
            'edit' => Pages\EditPayrollPeriod::route('/{record}/edit'),
        ];
    }
}
