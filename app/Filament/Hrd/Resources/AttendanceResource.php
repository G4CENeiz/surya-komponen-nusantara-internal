<?php

namespace App\Filament\Hrd\Resources;

use App\Filament\Hrd\Resources\AttendanceResource\Pages;
use App\Models\Attendance;
use App\Models\AttendanceCorrection;
use BackedEnum;
use Filament\Actions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;

class AttendanceResource extends Resource
{
    protected static ?string $model = Attendance::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-calendar-days';

    protected static string|UnitEnum|null $navigationGroup = 'Operasional';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Verifikasi Absensi';

    protected static ?string $recordTitleAttribute = 'date';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Textarea::make('notes')
                    ->label('Catatan HR')
                    ->rows(3)
                    ->placeholder('Tambahkan catatan verifikasi...'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('employee.full_name')
                    ->label('Karyawan')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('date')
                    ->label('Tanggal')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('clock_in_at')
                    ->label('Jam Masuk')
                    ->dateTime('H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('clock_out_at')
                    ->label('Jam Pulang')
                    ->dateTime('H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'pending_hr' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'pending_hr' => 'Menunggu Review HR',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                        default => ucfirst((string) $state),
                    }),
                Tables\Columns\IconColumn::make('is_late')
                    ->label('Terlambat')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_early_leave')
                    ->label('Pulang Cepat')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('worked_hours')
                    ->label('Jam Kerja')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('clock_in_within_geofence')
                    ->label('Masuk Geofence')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('clock_out_within_geofence')
                    ->label('Keluar Geofence')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending_hr' => 'Menunggu Review HR',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                    ]),
                Tables\Filters\Filter::make('date')
                    ->form([
                        DatePicker::make('date_from')
                            ->label('Dari')
                            ->native(false),
                        DatePicker::make('date_to')
                            ->label('Sampai')
                            ->native(false),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['date_from'], fn ($q, $date) => $q->whereDate('date', '>=', $date))
                            ->when($data['date_to'], fn ($q, $date) => $q->whereDate('date', '<=', $date));
                    }),
            ])
            ->actions([
                Actions\Action::make('approve')
                    ->label('Setujui')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Setujui Absensi')
                    ->modalDescription('Tandai catatan absensi ini sebagai terverifikasi dan disetujui.')
                    ->form([
                        Textarea::make('notes')
                            ->label('Catatan')
                            ->rows(2)
                            ->placeholder('Opsional...'),
                    ])
                    ->action(function (Attendance $record, array $data): void {
                        $record->update([
                            'status' => 'approved',
                            'is_verified' => true,
                            'verified_by' => auth()->id(),
                            'verified_at' => now(),
                            'notes' => $data['notes'] ?? null,
                        ]);
                    })
                    ->visible(fn (Attendance $record): bool => $record->status === 'pending_hr'),

                Actions\Action::make('reject')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Tolak Absensi')
                    ->modalDescription('Tolak catatan absensi ini.')
                    ->form([
                        Textarea::make('notes')
                            ->label('Alasan Penolakan')
                            ->rows(2)
                            ->required()
                            ->placeholder('Berikan alasan...'),
                    ])
                    ->action(function (Attendance $record, array $data): void {
                        $record->update([
                            'status' => 'rejected',
                            'is_verified' => true,
                            'verified_by' => auth()->id(),
                            'verified_at' => now(),
                            'notes' => $data['notes'],
                        ]);
                    })
                    ->visible(fn (Attendance $record): bool => $record->status !== 'rejected'),

                Actions\Action::make('correct')
                    ->label('Koreksi')
                    ->icon('heroicon-o-pencil-square')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Koreksi Absensi')
                    ->modalDescription('Ajukan koreksi untuk catatan absensi ini.')
                    ->form([
                        Textarea::make('correction_reason')
                            ->label('Alasan Koreksi')
                            ->rows(2)
                            ->required()
                            ->placeholder('Mengapa koreksi diperlukan?'),
                        Textarea::make('new_clock_in')
                            ->label('Jam Masuk Baru (H:i)')
                            ->rows(1)
                            ->placeholder('contoh: 08:00'),
                        Textarea::make('new_clock_out')
                            ->label('Jam Pulang Baru (H:i)')
                            ->rows(1)
                            ->placeholder('contoh: 17:00'),
                    ])
                    ->action(function (Attendance $record, array $data): void {
                        $oldData = [
                            'clock_in_at' => $record->clock_in_at?->format('H:i'),
                            'clock_out_at' => $record->clock_out_at?->format('H:i'),
                            'status' => $record->status,
                        ];

                        $newData = [];
                        if (! empty($data['new_clock_in'])) {
                            $record->update(['clock_in_at' => $record->date.' '.$data['new_clock_in']]);
                            $newData['clock_in_at'] = $data['new_clock_in'];
                        }
                        if (! empty($data['new_clock_out'])) {
                            $record->update(['clock_out_at' => $record->date.' '.$data['new_clock_out']]);
                            $newData['clock_out_at'] = $data['new_clock_out'];
                        }

                        AttendanceCorrection::create([
                            'attendance_id' => $record->id,
                            'corrected_by' => auth()->id(),
                            'correction_reason' => $data['correction_reason'],
                            'old_data' => $oldData,
                            'new_data' => $newData + ['status' => $record->status],
                        ]);

                        $record->update([
                            'status' => 'approved',
                            'is_verified' => true,
                            'verified_by' => auth()->id(),
                            'verified_at' => now(),
                            'notes' => 'Corrected: '.$data['correction_reason'],
                        ]);
                    }),
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
            'index' => Pages\ListAttendances::route('/'),
            'view' => Pages\ViewAttendance::route('/{record}'),
        ];
    }
}
