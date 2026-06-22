<?php

namespace App\Filament\Hrd\Resources;

use App\Filament\Hrd\Resources\UserResource\Pages;
use App\Models\User;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-user-group';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Data Pribadi')
                    ->schema([
                        TextInput::make('nik')
                            ->label('NIK')
                            ->unique(ignoreRecord: true)
                            ->maxLength(20),
                        TextInput::make('name')
                            ->label('Nama Lengkap')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        TextInput::make('password')
                            ->password()
                            ->dehydrateStateUsing(fn (string $state): string => Hash::make($state))
                            ->dehydrated(fn (?string $state): bool => filled($state))
                            ->required(fn (string $operation): bool => $operation === 'create'),
                        FileUpload::make('face_photo')
                            ->label('Foto Referensi Wajah')
                            ->disk('public')
                            ->directory('face-photos')
                            ->visibility('public')
                            ->image()
                            ->imageEditor()
                            ->columnSpanFull(),
                    ])->columns(2),

                Section::make('Data Kepegawaian')
                    ->schema([
                        Select::make('job_class_id')
                            ->label('Kelas Jabatan')
                            ->relationship('jobClass', 'name')
                            ->preload()
                            ->searchable()
                            ->required(),
                        TextInput::make('department')
                            ->label('Departemen')
                            ->maxLength(255),
                        Select::make('office_id')
                            ->label('Lokasi Kerja')
                            ->relationship('office', 'name')
                            ->preload()
                            ->searchable(),
                        Select::make('employment_status')
                            ->label('Status Kepegawaian')
                            ->options([
                                'active' => 'Aktif',
                                'on_leave' => 'Cuti',
                                'resigned' => 'Resign',
                            ])
                            ->default('active')
                            ->required(),
                        Select::make('roles')
                            ->label('Role')
                            ->relationship('roles', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->required(),
                    ])->columns(2),
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
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('jobClass.name')
                    ->label('Jabatan')
                    ->sortable(),
                Tables\Columns\TextColumn::make('department')
                    ->label('Departemen')
                    ->sortable(),
                Tables\Columns\TextColumn::make('office.name')
                    ->label('Lokasi')
                    ->sortable(),
                Tables\Columns\TextColumn::make('employment_status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'active' => 'Aktif',
                        'on_leave' => 'Cuti',
                        'resigned' => 'Resign',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'on_leave' => 'warning',
                        'resigned' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('roles.name')
                    ->label('Role')
                    ->badge()
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
