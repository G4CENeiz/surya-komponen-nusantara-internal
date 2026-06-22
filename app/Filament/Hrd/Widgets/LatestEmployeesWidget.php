<?php

namespace App\Filament\Hrd\Widgets;

use App\Models\Employee;
use Filament\Actions\EditAction;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class LatestEmployeesWidget extends TableWidget
{
    protected static ?string $heading = 'Latest Employees';

    protected static ?int $sort = 2;

    protected static ?string $maxHeight = '300px';

    public function table(Table $table): Table
    {
        return $table
            ->query(Employee::query()->with(['department', 'jobClass'])->latest())
            ->columns([
                Tables\Columns\TextColumn::make('full_name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('department.name')
                    ->label('Department'),
                Tables\Columns\TextColumn::make('jobClass.name')
                    ->label('Job Class'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'danger',
                        'on_leave' => 'warning',
                        'sick' => 'info',
                    }),
                Tables\Columns\TextColumn::make('hire_date')
                    ->label('Date Joined')
                    ->date()
                    ->sortable(),
            ])
            ->paginated([5])
            ->defaultPaginationPageOption(5)
            ->actions([
                EditAction::make(),
            ]);
    }
}
