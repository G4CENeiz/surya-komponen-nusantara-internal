<?php

namespace App\Filament\Hrd\Resources;

use App\Filament\Hrd\Forms\Components\LeafletMap;
use App\Filament\Hrd\Resources\WorkLocationResource\Pages;
use App\Models\Workplace;
use BackedEnum;
use Filament\Actions;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Slider;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;

class WorkLocationResource extends Resource
{
    protected static ?string $model = Workplace::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-map-pin';

    protected static string|UnitEnum|null $navigationGroup = 'Data Master';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Location Details')
                    ->schema([
                        TextInput::make('name')
                            ->label('Location Name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g. Jakarta HQ'),
                        TextInput::make('code')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->placeholder('e.g. JKT-001'),
                        Textarea::make('address')
                            ->rows(2)
                            ->required()
                            ->placeholder('Alamat lengkap'),
                    ])->columns(2),

                Section::make('Geofencing')
                    ->schema([
                        Hidden::make('latitude')
                            ->dehydrated()
                            ->default(-6.22689),
                        Hidden::make('longitude')
                            ->dehydrated()
                            ->default(106.81473),
                        Slider::make('radius_meters')
                            ->label('Radius Geofence (meter)')
                            ->minValue(10)
                            ->maxValue(100)
                            ->default(100)
                            ->suffix('m')
                            ->live()
                            ->tooltips()
                            ->displayValue(fn ($state): string => $state . ' m')
                            ->dehydrated(),
                        LeafletMap::make('geofence_map')
                            ->label('Pick Location & Geofence')
                            ->latStatePath('latitude')
                            ->lngStatePath('longitude')
                            ->radiusStatePath('radius_meters')
                            ->defaultLatLng(-6.22689, 106.81473)
                            ->columnSpanFull(),
                        Checkbox::make('is_active')
                            ->label('Aktif')
                            ->default(true),
                    ]),
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
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('address')
                    ->limit(40)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('radius_meters')
                    ->label('Radius')
                    ->suffix('m')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('employees_count')
                    ->counts('employees')
                    ->label('Karyawan')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Aktif'),
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
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWorkLocations::route('/'),
            'create' => Pages\CreateWorkLocation::route('/create'),
            'edit' => Pages\EditWorkLocation::route('/{record}/edit'),
        ];
    }
}
