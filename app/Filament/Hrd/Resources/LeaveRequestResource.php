<?php

namespace App\Filament\Hrd\Resources;

use App\Enums\LeaveStatus;
use App\Enums\LeaveType;
use App\Filament\Hrd\Resources\LeaveRequestResource\Pages;
use App\Models\LeaveRequest;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Placeholder;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class LeaveRequestResource extends Resource
{
    protected static ?string $model = LeaveRequest::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Pengajuan Cuti/Sakit/Lembur';

    protected static string|UnitEnum|null $navigationGroup = 'Approval';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Pengajuan')
                    ->schema([
                        Placeholder::make('user_name')
                            ->label('Pegawai')
                            ->content(fn (LeaveRequest $record): string => $record->user?->name ?? '—'),
                        Placeholder::make('type_label')
                            ->label('Jenis Pengajuan')
                            ->content(fn (LeaveRequest $record): string => $record->type?->label() ?? '—'),
                        Placeholder::make('start_date_display')
                            ->label('Tanggal Mulai')
                            ->content(fn (LeaveRequest $record): string => $record->start_date?->format('d M Y') ?? '—'),
                        Placeholder::make('end_date_display')
                            ->label('Tanggal Selesai')
                            ->content(fn (LeaveRequest $record): string => $record->end_date?->format('d M Y') ?? '—'),
                        Placeholder::make('time_display')
                            ->label('Jam (Lembur)')
                            ->content(function (LeaveRequest $record): string {
                                if ($record->type !== LeaveType::Overtime || ! $record->start_time) {
                                    return '—';
                                }

                                return $record->start_time.' - '.($record->end_time ?? '—');
                            }),
                        Placeholder::make('reason_display')
                            ->label('Alasan')
                            ->content(fn (LeaveRequest $record): string => $record->reason ?? '—'),
                        Placeholder::make('attachment_display')
                            ->label('Lampiran')
                            ->content(function (LeaveRequest $record): string {
                                if (! $record->attachment_path) {
                                    return 'Tidak ada lampiran';
                                }

                                $url = asset('storage/'.$record->attachment_path);

                                return "<a href=\"{$url}\" target=\"_blank\" class=\"text-primary-600 underline\">Lihat Lampiran</a>";
                            }),
                    ])->columns(2),

                Section::make('Review HR')
                    ->schema([
                        Select::make('status')
                            ->options(LeaveStatus::class)
                            ->label('Keputusan')
                            ->required(),
                        Textarea::make('hr_notes')
                            ->label('Catatan HR')
                            ->rows(3)
                            ->columnSpanFull(),
                        DateTimePicker::make('reviewed_at')
                            ->label('Waktu Review')
                            ->default(now()),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Pegawai')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Jenis')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => $state instanceof LeaveType ? $state->label() : (string) $state)
                    ->color(fn ($state): string => match ($state instanceof LeaveType ? $state : LeaveType::from((string) $state)) {
                        LeaveType::AnnualLeave => 'info',
                        LeaveType::SickLeave => 'warning',
                        LeaveType::Overtime => 'success',
                    }),
                Tables\Columns\TextColumn::make('start_date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_date')
                    ->label('Sampai')
                    ->date('d M Y'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => $state instanceof LeaveStatus ? $state->label() : ucfirst((string) $state))
                    ->color(fn ($state): string => $state instanceof LeaveStatus ? $state->color() : 'gray'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Diajukan')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options(LeaveType::class)
                    ->label('Jenis'),
                Tables\Filters\SelectFilter::make('status')
                    ->options(LeaveStatus::class)
                    ->label('Status'),
                Tables\Filters\Filter::make('pending')
                    ->label('Menunggu Persetujuan')
                    ->query(fn (Builder $query): Builder => $query->where('status', 'pending'))
                    ->toggle(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Action::make('approve')
                    ->label('Setujui')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (LeaveRequest $record): bool => $record->status === LeaveStatus::Pending)
                    ->requiresConfirmation()
                    ->form([
                        Textarea::make('hr_notes')
                            ->label('Catatan (opsional)')
                            ->rows(2),
                    ])
                    ->action(function (LeaveRequest $record, array $data): void {
                        $record->update([
                            'status' => LeaveStatus::Approved,
                            'reviewed_by' => auth()->id(),
                            'reviewed_at' => now(),
                            'hr_notes' => $data['hr_notes'] ?? null,
                        ]);

                        Notification::make()
                            ->title('Pengajuan disetujui')
                            ->success()
                            ->send();
                    }),
                Action::make('reject')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (LeaveRequest $record): bool => $record->status === LeaveStatus::Pending)
                    ->requiresConfirmation()
                    ->form([
                        Textarea::make('hr_notes')
                            ->label('Alasan Penolakan')
                            ->required()
                            ->rows(2),
                    ])
                    ->action(function (LeaveRequest $record, array $data): void {
                        $record->update([
                            'status' => LeaveStatus::Rejected,
                            'reviewed_by' => auth()->id(),
                            'reviewed_at' => now(),
                            'hr_notes' => $data['hr_notes'],
                        ]);

                        Notification::make()
                            ->title('Pengajuan ditolak')
                            ->danger()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    BulkAction::make('bulkApprove')
                        ->label('Setujui Semua')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function ($records): void {
                            foreach ($records as $record) {
                                if ($record->status === LeaveStatus::Pending) {
                                    $record->update([
                                        'status' => LeaveStatus::Approved,
                                        'reviewed_by' => auth()->id(),
                                        'reviewed_at' => now(),
                                    ]);
                                }
                            }

                            Notification::make()
                                ->title(count($records).' pengajuan disetujui')
                                ->success()
                                ->send();
                        }),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLeaveRequests::route('/'),
            'view' => Pages\ViewLeaveRequest::route('/{record}'),
        ];
    }
}
