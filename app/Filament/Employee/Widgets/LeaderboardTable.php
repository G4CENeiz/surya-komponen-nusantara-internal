<?php

namespace App\Filament\Employee\Widgets;

use App\Models\Attendance;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class LeaderboardTable extends TableWidget
{
    protected static string|bool $navigationIcon = false;

    protected static ?string $navigationLabel = null;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $today = now()->toDateString();
        $userId = auth()->id();

        return $table
            ->query(
                Attendance::whereDate('date', $today)
                    ->whereNotNull('clock_in_at')
                    ->join('users', 'attendances.user_id', '=', 'users.id')
                    ->select('attendances.*', 'users.name')
                    ->orderBy('attendances.clock_in_at', 'asc')
            )
            ->columns([
                Tables\Columns\TextColumn::make('rank')
                    ->label('#')
                    ->state(fn ($record, $rowLoop) => $rowLoop->iteration)
                    ->sortable(false)
                    ->alignCenter()
                    ->weight('bold')
                    ->width(50),
                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->sortable(false)
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('clock_in_at')
                    ->label('Clock In')
                    ->dateTime('H:i:s')
                    ->sortable('clock_in_at')
                    ->alignCenter(),
            ])
            ->paginated(false)
            ->defaultSort('clock_in_at', 'asc');
    }
}
