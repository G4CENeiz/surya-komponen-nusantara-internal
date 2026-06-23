<?php

namespace App\Filament\Hrd\Resources;

use App\Filament\Hrd\Resources\JobClassResource\Pages;
use App\Models\JobClass;
use BackedEnum;
use Filament\Actions;
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

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-briefcase';

    protected static string|UnitEnum|null $navigationGroup = 'Data Master';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Job Title')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('contoh: Staff, Supervisor, Manager'),
                TextInput::make('code')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255)
                    ->placeholder('contoh: STF-001'),
                TextInput::make('level')
                    ->required()
                    ->numeric()
                    ->integer()
                    ->minValue(1)
                    ->placeholder('1, 2, 3...'),
                TextInput::make('min_salary')
                    ->label('Base Salary (Min)')
                    ->required()
                    ->numeric()
                    ->prefix('Rp')
                    ->placeholder('3500000'),
                TextInput::make('max_salary')
                    ->label('Base Salary (Max)')
                    ->required()
                    ->numeric()
                    ->prefix('Rp')
                    ->placeholder('5500000'),
                TextInput::make('base_allowance')
                    ->label('Allowance')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->prefix('Rp')
                    ->placeholder('500000'),
                Textarea::make('description')
                    ->rows(3)
                    ->placeholder('Job class description...'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Job Title')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('level')
                    ->sortable()
                    ->badge(),
                Tables\Columns\TextColumn::make('min_salary')
                    ->label('Base Salary (Min)')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('max_salary')
                    ->label('Base Salary (Max)')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('base_allowance')
                    ->label('Allowance')
                    ->money('IDR'),
                Tables\Columns\TextColumn::make('employees_count')
                    ->counts('employees')
                    ->label('Employees')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
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
