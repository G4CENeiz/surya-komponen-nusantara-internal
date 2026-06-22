<?php

namespace App\Filament\Hrd\Widgets;

use App\Models\Submission;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class PendingSubmissionsWidget extends TableWidget
{
    protected static ?string $heading = 'Pending Approvals';

    protected static ?int $sort = 5;

    protected ?string $maxHeight = '300px';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Submission::with('employee')
                    ->where('status', 'pending')
                    ->latest(),
            )
            ->columns([
                Tables\Columns\TextColumn::make('employee.full_name')
                    ->label('Employee')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('employee.nik')
                    ->label('NIK')
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'leave' => 'info',
                        'sick' => 'warning',
                        'overtime' => 'success',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'leave' => 'Leave',
                        'sick' => 'Sick',
                        'overtime' => 'Overtime',
                    }),
                Tables\Columns\TextColumn::make('start_date')
                    ->label('Start')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_date')
                    ->label('End')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Requested')
                    ->since()
                    ->sortable(),
            ])
            ->paginated([5])
            ->defaultPaginationPageOption(5);
    }
}
