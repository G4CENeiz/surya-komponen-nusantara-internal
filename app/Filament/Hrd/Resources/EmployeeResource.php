<?php

namespace App\Filament\Hrd\Resources;

use App\Filament\Hrd\Resources\EmployeeResource\Pages;
use App\Models\Employee;
use BackedEnum;
use Filament\Actions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;

class EmployeeResource extends Resource
{
    protected static ?string $model = Employee::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-users';

    protected static string|UnitEnum|null $navigationGroup = 'Master Data';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'full_name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Wizard::make([
                    Step::make('Personal Information')
                        ->icon('heroicon-o-user')
                        ->schema([
                            Grid::make(3)
                                ->schema([
                                    TextInput::make('nik')
                                        ->label('NIK')
                                        ->validationAttribute('NIK')
                                        ->required()
                                        ->unique(ignoreRecord: true)
                                        ->maxLength(255)
                                        ->placeholder('e.g. NIK-00001'),
                                    TextInput::make('full_name')
                                        ->label('Name')
                                        ->required()
                                        ->maxLength(255),
                                    Select::make('gender')
                                        ->options([
                                            'male' => 'Male',
                                            'female' => 'Female',
                                        ])
                                        ->required(),
                                ]),
                            Grid::make(3)
                                ->schema([
                                    TextInput::make('place_of_birth')
                                        ->maxLength(255)
                                        ->placeholder('City of birth'),
                                    DatePicker::make('date_of_birth'),
                                    Select::make('status')
                                        ->label('Status')
                                        ->options([
                                            'active' => 'Active',
                                            'inactive' => 'Inactive',
                                            'on_leave' => 'On Leave',
                                            'sick' => 'Sick',
                                        ])
                                        ->required()
                                        ->default('active'),
                                ]),
                            Grid::make(2)
                                ->schema([
                                    TextInput::make('phone')
                                        ->tel()
                                        ->maxLength(255),
                                    TextInput::make('office_email')
                                        ->email()
                                        ->maxLength(255),
                                ]),
                            Textarea::make('address')
                                ->rows(2)
                                ->placeholder('Residential address'),
                        ]),

                    Step::make('Employment Details')
                        ->icon('heroicon-o-briefcase')
                        ->schema([
                            Grid::make(3)
                                ->schema([
                                    Select::make('user_id')
                                        ->relationship('user', 'name')
                                        ->searchable()
                                        ->preload()
                                        ->required()
                                        ->label('User Account'),
                                    Select::make('department_id')
                                        ->relationship('department', 'name')
                                        ->label('Department')
                                        ->searchable()
                                        ->preload()
                                        ->required(),
                                    Select::make('job_class_id')
                                        ->relationship('jobClass', 'name')
                                        ->label('Job Class')
                                        ->searchable()
                                        ->preload()
                                        ->required(),
                                ]),
                            Grid::make(3)
                                ->schema([
                                    Select::make('work_location_id')
                                        ->relationship('workLocation', 'name')
                                        ->searchable()
                                        ->preload()
                                        ->required(),
                                    DatePicker::make('hire_date')
                                        ->required(),
                                    DatePicker::make('termination_date'),
                                ]),
                        ]),

                    Step::make('Work Location')
                        ->icon('heroicon-o-map-pin')
                        ->schema([
                            Grid::make(2)
                                ->schema([
                                    // Latitude and longitude are stored in work_locations table
                                ]),
                        ]),

                    Step::make('Face Reference')
                        ->icon('heroicon-o-camera')
                        ->description('Upload a face reference photo for future face recognition integration')
                        ->schema([
                            FileUpload::make('face_photo_path')
                                ->label('Face Reference')
                                ->image()
                                ->imageEditor()
                                ->directory('face-references')
                                ->visibility('public')
                                ->maxSize(5120)
                                ->helperText('Max 5MB. Accepted: jpg, png'),
                        ]),
                ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nik')
                    ->label('NIK')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('full_name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('department.name')
                    ->label('Department')
                    ->sortable(),
                Tables\Columns\TextColumn::make('jobClass.name')
                    ->label('Job Class')
                    ->sortable(),
                Tables\Columns\TextColumn::make('workLocation.name')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'danger',
                        'on_leave' => 'warning',
                        'sick' => 'info',
                    }),
                Tables\Columns\TextColumn::make('hire_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                        'on_leave' => 'On Leave',
                        'sick' => 'Sick',
                    ]),
                Tables\Filters\SelectFilter::make('department_id')
                    ->relationship('department', 'name')
                    ->label('Department'),
                Tables\Filters\SelectFilter::make('job_class_id')
                    ->relationship('jobClass', 'name')
                    ->label('Job Class'),
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
            'index' => Pages\ListEmployees::route('/'),
            'create' => Pages\CreateEmployee::route('/create'),
            'edit' => Pages\EditEmployee::route('/{record}/edit'),
        ];
    }
}
