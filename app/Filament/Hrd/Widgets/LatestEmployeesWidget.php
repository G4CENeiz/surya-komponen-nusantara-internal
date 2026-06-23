<?php

namespace App\Filament\Hrd\Widgets;

use App\Models\Employee;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class LatestEmployeesWidget extends TableWidget
{
    protected static ?string $heading = 'Karyawan Terbaru';

    protected static ?int $sort = 2;

    protected ?string $maxHeight = '300px';

    protected int|string|array $columnSpan = 'half';

    public function table(Table $table): Table
    {
        return $table
            ->query(Employee::query()->with(['department', 'jobClass'])->latest('hire_date'))
            ->columns([
                Tables\Columns\TextColumn::make('full_name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('department.name')
                    ->label('Dept')
                    ->limit(12),
                Tables\Columns\TextColumn::make('jobClass.name')
                    ->label('Jabatan')
                    ->limit(12),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'active' => 'Aktif',
                        'inactive' => 'Non-aktif',
                        'on_leave' => 'Cuti',
                        'sick' => 'Sakit',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'danger',
                        'on_leave' => 'warning',
                        'sick' => 'info',
                    }),
                Tables\Columns\TextColumn::make('hire_date')
                    ->label('Tanggal Masuk')
                    ->date()
                    ->sortable(),
            ])
            ->paginated([5])
            ->defaultPaginationPageOption(5);
    }
}
