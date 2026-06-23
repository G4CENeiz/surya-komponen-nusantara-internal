<?php

namespace App\Filament\Hrd\Resources;

use App\Filament\Hrd\Resources\AssignmentResource\Pages;
use App\Models\Assignment;
use App\Models\Department;
use App\Models\Employee;
use BackedEnum;
use Filament\Actions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;

class AssignmentResource extends Resource
{
    protected static ?string $model = Assignment::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static string|UnitEnum|null $navigationGroup = 'Komunikasi';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Penugasan';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->label('Judul')
                    ->required()
                    ->maxLength(255),
                RichEditor::make('description')
                    ->label('Deskripsi & Instruksi')
                    ->required()
                    ->columnSpanFull(),
                Select::make('department_filter')
                    ->label('Filter per Departemen')
                    ->options(fn () => Department::pluck('name', 'id'))
                    ->searchable()
                    ->preload()
                    ->reactive()
                    ->afterStateUpdated(fn ($state, $set) => $set('assigned_to', null))
                    ->dehydrated(false),
                Select::make('assigned_to')
                    ->label('Ditugaskan Ke')
                    ->options(fn (callable $get) => Employee::query()
                        ->with('user')
                        ->when($get('department_filter'), fn ($query, $deptId) => $query->where('department_id', $deptId))
                        ->get()
                        ->mapWithKeys(fn ($emp) => [$emp->user_id => $emp->full_name.' ('.$emp->nik.')']))
                    ->searchable()
                    ->required(),
                DatePicker::make('start_date')
                    ->label('Tanggal Mulai')
                    ->required(),
                DatePicker::make('end_date')
                    ->label('Tanggal Selesai')
                    ->required(),
                Textarea::make('notes')
                    ->label('Catatan')
                    ->rows(3)
                    ->placeholder('Catatan atau komentar tambahan...'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('assignee.name')
                    ->label('Ditugaskan Ke')
                    ->sortable(),
                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Dibuat Oleh')
                    ->placeholder('Tidak Diketahui')
                    ->sortable(),
                Tables\Columns\TextColumn::make('start_date')
                    ->label('Tanggal Mulai')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_date')
                    ->label('Tanggal Selesai')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([])
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
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAssignments::route('/'),
            'create' => Pages\CreateAssignment::route('/create'),
            'edit' => Pages\EditAssignment::route('/{record}/edit'),
        ];
    }
}
