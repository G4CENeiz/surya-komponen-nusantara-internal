<?php

namespace App\Filament\Hrd\Resources;

use App\Filament\Hrd\Resources\JobClassResource\Pages;
use App\Models\JobClass;
use BackedEnum;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;

class JobClassResource extends Resource
{
    protected static ?string $model = JobClass::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationLabel = 'Kelas Jabatan';

    protected static string|UnitEnum|null $navigationGroup = 'Master Data';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nama Kelas Jabatan')
                    ->required()
                    ->maxLength(255),
                TextInput::make('base_salary')
                    ->label('Gaji Pokok')
                    ->numeric()
                    ->prefix('Rp')
                    ->required(),
                TextInput::make('allowance')
                    ->label('Tunjangan')
                    ->numeric()
                    ->prefix('Rp')
                    ->default(0),
                Textarea::make('description')
                    ->label('Deskripsi')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('base_salary')
                    ->label('Gaji Pokok')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('allowance')
                    ->label('Tunjangan')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('users_count')
                    ->label('Jumlah Pegawai')
                    ->counts('users')
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListJobClasses::route('/'),
            'create' => Pages\CreateJobClass::route('/create'),
            'edit' => Pages\EditJobClass::route('/{record}/edit'),
        ];
    }
}
