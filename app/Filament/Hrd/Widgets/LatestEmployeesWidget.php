<?php

namespace App\Filament\Hrd\Widgets;

use App\Models\Employee;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class LatestEmployeesWidget extends TableWidget
{
    protected static ?string $heading = 'Recent Hires';

    protected static ?int $sort = 2;

    protected ?string $maxHeight = '300px';

    protected int|string|array $columnSpan = 'half';

    public function table(Table $table): Table
    {
        return $table
            ->query(Employee::query()->with(['department', 'jobClass'])->latest('hire_date'))
            ->columns([
                Tables\Columns\TextColumn::make('full_name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('department.name')
                    ->label('Dept')
                    ->limit(12),
                Tables\Columns\TextColumn::make('jobClass.name')
                    ->label('Job')
                    ->limit(12),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'danger',
                        'on_leave' => 'warning',
                        'sick' => 'info',
                    }),
                Tables\Columns\TextColumn::make('hire_date')
                    ->label('Joined')
                    ->date()
                    ->sortable(),
            ])
            ->paginated([5])
            ->defaultPaginationPageOption(5);
    }
}
