<?php

namespace App\Filament\Employee\Pages;

use App\Models\Announcement;
use BackedEnum;
use Filament\Actions\ViewAction;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;

class Announcements extends Page implements Tables\Contracts\HasTable
{
    use Tables\Concerns\InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-megaphone';

    protected static ?string $navigationLabel = 'Pengumuman';

    protected static ?string $title = 'Pengumuman';

    protected string $view = 'filament.employee.pages.announcements';

    public function table(Table $table): Table
    {
        return $table
            ->query(Announcement::whereNotNull('published_at')->latest('published_at'))
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('body')
                    ->label('Isi')
                    ->limit(80)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Penulis'),
                Tables\Columns\TextColumn::make('published_at')
                    ->label('Tanggal')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('published_at', 'desc')
            ->paginated([10])
            ->actions([
                ViewAction::make(),
            ]);
    }
}
