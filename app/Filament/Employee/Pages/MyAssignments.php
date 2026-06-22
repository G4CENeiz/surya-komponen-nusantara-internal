<?php

namespace App\Filament\Employee\Pages;

use App\Models\Assignment;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;

class MyAssignments extends Page implements Tables\Contracts\HasTable
{
    use Tables\Concerns\InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationLabel = 'Penugasan Saya';

    protected static ?string $title = 'Penugasan Saya';

    protected string $view = 'filament.employee.pages.my-assignments';

    public function table(Table $table): Table
    {
        return $table
            ->query(Assignment::where('user_id', auth()->id()))
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Tugas')
                    ->searchable(),
                Tables\Columns\TextColumn::make('description')
                    ->label('Deskripsi')
                    ->limit(50)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('due_date')
                    ->label('Deadline')
                    ->date('d M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Menunggu',
                        'in_progress' => 'Dikerjakan',
                        'completed' => 'Selesai',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'in_progress' => 'info',
                        'completed' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Ditugaskan Oleh'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d M Y')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([10]);
    }
}
