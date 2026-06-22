<?php

namespace App\Filament\Hrd\Resources;

use App\Enums\AttendanceStatus;
use App\Filament\Hrd\Resources\AttendanceResource\Pages;
use App\Models\Attendance;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Html;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class AttendanceResource extends Resource
{
    protected static ?string $model = Attendance::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationLabel = 'Attendance Review';

    protected static string|UnitEnum|null $navigationGroup = 'Attendance';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Employee Information')
                    ->schema([
                        Placeholder::make('employee_name')
                            ->label('Employee')
                            ->content(fn (Attendance $record): string => $record->user?->name ?? '—'),
                        Placeholder::make('date')
                            ->label('Date')
                            ->content(fn (Attendance $record): string => $record->date?->format('M d, Y') ?? '—'),
                    ])->columns(2),

                Section::make('Clock In')
                    ->schema([
                        DateTimePicker::make('clock_in_at')
                            ->label('Time')
                            ->disabled(),
                        Html::make(function (Attendance $record): string {
                            if (! $record->clock_in_photo_path) {
                                return '<p class="text-sm text-gray-500">No photo uploaded</p>';
                            }

                            $url = asset('storage/'.$record->clock_in_photo_path);

                            return "<img src=\"{$url}\" alt=\"Clock In Photo\" style=\"max-height:200px;border-radius:8px;\" />";
                        }),
                        Placeholder::make('clock_in_face_confidence_display')
                            ->label('Face Confidence')
                            ->content(fn (Attendance $record): string => $record->clock_in_face_confidence !== null
                                ? number_format($record->clock_in_face_confidence * 100, 1).'%'
                                : '—'),
                        Placeholder::make('clock_in_geofence_display')
                            ->label('Geofence')
                            ->content(fn (Attendance $record): string => match ($record->clock_in_within_geofence) {
                                true => '✅ Inside',
                                false => '❌ Outside',
                                default => '—',
                            }),
                        Placeholder::make('clock_in_method_display')
                            ->label('Method')
                            ->content(fn (Attendance $record): string => ucfirst($record->clock_in_method ?? '—')),
                    ])->columns(2),

                Section::make('Clock Out')
                    ->schema([
                        DateTimePicker::make('clock_out_at')
                            ->label('Time')
                            ->disabled(),
                        Html::make(function (Attendance $record): string {
                            if (! $record->clock_out_photo_path) {
                                return '<p class="text-sm text-gray-500">No photo uploaded</p>';
                            }

                            $url = asset('storage/'.$record->clock_out_photo_path);

                            return "<img src=\"{$url}\" alt=\"Clock Out Photo\" style=\"max-height:200px;border-radius:8px;\" />";
                        }),
                        Placeholder::make('clock_out_face_confidence_display')
                            ->label('Face Confidence')
                            ->content(fn (Attendance $record): string => $record->clock_out_face_confidence !== null
                                ? number_format($record->clock_out_face_confidence * 100, 1).'%'
                                : '—'),
                        Placeholder::make('clock_out_geofence_display')
                            ->label('Geofence')
                            ->content(fn (Attendance $record): string => match ($record->clock_out_within_geofence) {
                                true => '✅ Inside',
                                false => '❌ Outside',
                                default => '—',
                            }),
                        Placeholder::make('clock_out_method_display')
                            ->label('Method')
                            ->content(fn (Attendance $record): string => ucfirst($record->clock_out_method ?? '—')),
                    ])->columns(2),

                Section::make('Suspicious Photo Flag')
                    ->schema([
                        Toggle::make('is_suspicious')
                            ->label('Flag as Suspicious')
                            ->helperText('Enable this if the attendance photo looks manipulated, reused, or does not match the employee.'),
                        Textarea::make('suspicious_reason')
                            ->label('Reason for Suspicion')
                            ->placeholder('e.g. Photo appears to be a screenshot, face does not match employee, same photo used multiple times...')
                            ->rows(3)
                            ->columnSpanFull()
                            ->visible(fn ($state, $get): bool => $get('is_suspicious')),
                    ]),

                Section::make('HR Review')
                    ->schema([
                        Select::make('status')
                            ->options(AttendanceStatus::class)
                            ->label('Status')
                            ->required(),
                        Textarea::make('hr_notes')
                            ->label('HR Notes')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),

                Section::make('Additional Details')
                    ->collapsed()
                    ->schema([
                        Placeholder::make('worked_hours_display')
                            ->label('Worked Hours')
                            ->content(fn (Attendance $record): string => $record->worked_hours !== null
                                ? number_format((float) $record->worked_hours, 2).'h'
                                : '—'),
                        Placeholder::make('is_late_display')
                            ->label('Late')
                            ->content(fn (Attendance $record): string => $record->is_late ? '⚠️ Yes' : 'No'),
                        Placeholder::make('is_early_leave_display')
                            ->label('Early Leave')
                            ->content(fn (Attendance $record): string => $record->is_early_leave ? '⚠️ Yes' : 'No'),
                        Placeholder::make('verified_by_display')
                            ->label('Verified By')
                            ->content(fn (Attendance $record): string => $record->verifier?->name ?? '—'),
                        Placeholder::make('verified_at_display')
                            ->label('Verified At')
                            ->content(fn (Attendance $record): string => $record->verified_at?->format('M d, Y H:i') ?? '—'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Employee')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('date')
                    ->date('M d, Y')
                    ->sortable(),
                TextColumn::make('clock_in_at')
                    ->dateTime('H:i')
                    ->label('In'),
                TextColumn::make('clock_out_at')
                    ->dateTime('H:i')
                    ->label('Out'),
                TextColumn::make('status')
                    ->badge()
                    ->label('Status')
                    ->formatStateUsing(fn ($state): string => $state instanceof AttendanceStatus ? $state->label() : ucfirst(str_replace('_', ' ', (string) $state)))
                    ->color(fn ($state): string => $state instanceof AttendanceStatus ? $state->color() : 'gray'),
                TextColumn::make('is_suspicious')
                    ->label('Suspicious')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => $state ? '⚠️ Flagged' : 'Clean')
                    ->color(fn ($state): string => $state ? 'danger' : 'success')
                    ->sortable(),
                TextColumn::make('clock_in_face_confidence')
                    ->label('Face Match')
                    ->formatStateUsing(fn ($state): string => $state !== null ? number_format($state * 100, 0).'%' : '—')
                    ->color(fn ($state): string => $state === null ? 'gray' : ($state >= 0.7 ? 'success' : ($state >= 0.4 ? 'warning' : 'danger')))
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('clock_in_within_geofence')
                    ->label('Geofence')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => match ($state) {
                        true => 'Inside',
                        false => 'Outside',
                        default => '—',
                    })
                    ->color(fn ($state): string => match ($state) {
                        true => 'success',
                        false => 'danger',
                        default => 'gray',
                    })
                    ->toggleable(),
                TextColumn::make('worked_hours')
                    ->suffix('h')
                    ->label('Hours')
                    ->toggleable(),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('date', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options(AttendanceStatus::class)
                    ->label('Status'),
                Filter::make('is_suspicious')
                    ->label('Suspicious Only')
                    ->query(fn (Builder $query): Builder => $query->where('is_suspicious', true))
                    ->toggle(),
                Filter::make('outside_geofence')
                    ->label('Outside Geofence')
                    ->query(fn (Builder $query): Builder => $query->where('clock_in_within_geofence', false)->orWhere('clock_out_within_geofence', false))
                    ->toggle(),
                Filter::make('low_face_confidence')
                    ->label('Low Face Confidence (<70%)')
                    ->query(fn (Builder $query): Builder => $query->where('clock_in_face_confidence', '<', 0.7)->orWhere('clock_out_face_confidence', '<', 0.7))
                    ->toggle(),
            ])
            ->actions([
                ViewAction::make(),
                Action::make('flagSuspicious')
                    ->label('Flag')
                    ->icon('heroicon-o-flag')
                    ->color('danger')
                    ->visible(fn (Attendance $record): bool => ! $record->is_suspicious)
                    ->form([
                        Textarea::make('suspicious_reason')
                            ->label('Reason')
                            ->required()
                            ->placeholder('Why is this attendance suspicious?'),
                    ])
                    ->action(function (Attendance $record, array $data): void {
                        $record->update([
                            'is_suspicious' => true,
                            'suspicious_reason' => $data['suspicious_reason'],
                        ]);

                        Notification::make()
                            ->title('Attendance flagged as suspicious')
                            ->danger()
                            ->send();
                    })
                    ->requiresConfirmation(),
                Action::make('unflagSuspicious')
                    ->label('Unflag')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Attendance $record): bool => $record->is_suspicious)
                    ->action(function (Attendance $record): void {
                        $record->update([
                            'is_suspicious' => false,
                            'suspicious_reason' => null,
                        ]);

                        Notification::make()
                            ->title('Suspicious flag removed')
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('bulkFlagSuspicious')
                        ->label('Flag as Suspicious')
                        ->icon('heroicon-o-flag')
                        ->color('danger')
                        ->form([
                            Textarea::make('suspicious_reason')
                                ->label('Reason')
                                ->required(),
                        ])
                        ->action(function (array $records, array $data): void {
                            foreach ($records as $record) {
                                $record->update([
                                    'is_suspicious' => true,
                                    'suspicious_reason' => $data['suspicious_reason'],
                                ]);
                            }

                            Notification::make()
                                ->title(count($records).' attendance(s) flagged')
                                ->danger()
                                ->send();
                        })
                        ->requiresConfirmation(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAttendances::route('/'),
            'view' => Pages\ViewAttendance::route('/{record}'),
            'edit' => Pages\EditAttendance::route('/{record}/edit'),
        ];
    }
}
