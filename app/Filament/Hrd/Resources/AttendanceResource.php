<?php

namespace App\Filament\Hrd\Resources;

use App\Enums\AttendanceStatus;
use App\Filament\Hrd\Resources\AttendanceResource\Pages;
use App\Models\Attendance;
use App\Models\AttendanceCorrection;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TimePicker;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class AttendanceResource extends Resource
{
    protected static ?string $model = Attendance::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationLabel = 'Attendance Logs';

    protected static ?string $modelLabel = 'Attendance Log';

    protected static ?string $pluralModelLabel = 'Attendance Logs';

    protected static ?int $navigationSort = 21;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('employee_id')
                    ->label('Employee')
                    ->relationship('employee', 'full_name')
                    ->searchable()
                    ->preload()
                    ->required(),
                DatePicker::make('date')
                    ->label('Date')
                    ->required(),
                TimePicker::make('clock_in')
                    ->label('Check-in Time')
                    ->seconds(false),
                TimePicker::make('clock_out')
                    ->label('Check-out Time')
                    ->seconds(false),
                Select::make('status')
                    ->label('Status')
                    ->options(AttendanceStatus::class)
                    ->required(),
                Textarea::make('notes')
                    ->label('Notes')
                    ->rows(2)
                    ->placeholder('Notes for this attendance record...')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('employee.full_name')
                    ->label('Employee')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('date')
                    ->date('d M Y')
                    ->sortable()
                    ->label('Date'),
                Tables\Columns\TextColumn::make('clock_in')
                    ->label('Check-in')
                    ->sortable(),
                Tables\Columns\TextColumn::make('clock_out')
                    ->label('Check-out')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (AttendanceStatus $state): string => $state->label())
                    ->color(fn (AttendanceStatus $state): string => $state->color())
                    ->sortable()
                    ->label('Status'),
                Tables\Columns\IconColumn::make('clock_in_within_geofence')
                    ->boolean()
                    ->label('In Radius'),
                Tables\Columns\ImageColumn::make('clock_in_photo_path')
                    ->disk('public')
                    ->circular()
                    ->limit(1)
                    ->label('Face (In)'),
                Tables\Columns\IconColumn::make('clock_out_within_geofence')
                    ->boolean()
                    ->label('Out Radius'),
                Tables\Columns\ImageColumn::make('clock_out_photo_path')
                    ->disk('public')
                    ->circular()
                    ->limit(1)
                    ->label('Face (Out)'),
                Tables\Columns\IconColumn::make('is_verified')
                    ->boolean()
                    ->label('Verified'),
            ])
            ->filters([
                Tables\Filters\Filter::make('date')
                    ->form([
                        DatePicker::make('date_from')
                            ->label('From'),
                        DatePicker::make('date_until')
                            ->label('Until'),
                    ])
                    ->query(function ($query, array $data): void {
                        $query
                            ->when($data['date_from'], fn ($q, $date) => $q->whereDate('date', '>=', $date))
                            ->when($data['date_until'], fn ($q, $date) => $q->whereDate('date', '<=', $date));
                    })
                    ->label('Date Range'),
                Tables\Filters\SelectFilter::make('status')
                    ->options(AttendanceStatus::class)
                    ->label('Status'),
                Tables\Filters\TernaryFilter::make('is_verified')
                    ->label('Verification')
                    ->boolean()
                    ->trueLabel('Verified Only')
                    ->falseLabel('Unverified Only'),
            ])
            ->actions([
                Action::make('verify')
                    ->label('Verify')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Verify Attendance')
                    ->modalDescription('Confirm this attendance record is accurate and within policy.')
                    ->modalSubmitActionLabel('Yes, Verify')
                    ->action(fn ($record) => $record->update([
                        'is_verified' => true,
                        'verified_by' => auth()->id(),
                        'verified_at' => now(),
                    ]))
                    ->visible(fn ($record): bool => ! $record->is_verified),
                Action::make('correctData')
                    ->label('Correct Data')
                    ->icon('heroicon-o-pencil')
                    ->color('primary')
                    ->form([
                        TimePicker::make('clock_in')
                            ->label('Check-in Time')
                            ->seconds(false)
                            ->required(),
                        TimePicker::make('clock_out')
                            ->label('Check-out Time')
                            ->seconds(false),
                        Select::make('status')
                            ->label('Status')
                            ->options(AttendanceStatus::class)
                            ->required(),
                        Textarea::make('notes')
                            ->label('Correction Reason')
                            ->rows(2)
                            ->placeholder('Reason for correction...'),
                    ])
                    ->action(function (Attendance $record, array $data): void {
                        $oldData = [
                            'clock_in' => $record->clock_in,
                            'clock_out' => $record->clock_out,
                            'status' => $record->status?->value,
                            'notes' => $record->notes,
                        ];

                        $record->update([
                            'clock_in' => $data['clock_in'],
                            'clock_out' => $data['clock_out'] ?? $record->clock_out,
                            'status' => $data['status'],
                            'notes' => $data['notes'] ?? $record->notes,
                            'is_verified' => true,
                            'verified_by' => auth()->id(),
                            'verified_at' => now(),
                        ]);

                        AttendanceCorrection::create([
                            'attendance_id' => $record->id,
                            'corrected_by' => auth()->id(),
                            'correction_reason' => $data['notes'] ?? 'HR correction',
                            'old_data' => $oldData,
                            'new_data' => [
                                'clock_in' => $data['clock_in'],
                                'clock_out' => $data['clock_out'] ?? $record->clock_out,
                                'status' => $data['status'],
                                'notes' => $data['notes'] ?? $record->notes,
                            ],
                        ]);
                    })
                    ->modalHeading('Correct Attendance Data')
                    ->modalDescription('Override check-in, check-out, or status for this attendance record.')
                    ->modalSubmitActionLabel('Save Correction'),
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
            'index' => Pages\ListAttendances::route('/'),
        ];
    }
}
