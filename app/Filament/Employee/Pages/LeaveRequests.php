<?php

namespace App\Filament\Employee\Pages;

use App\Enums\LeaveType;
use App\Models\LeaveRequest;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TimePicker;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;

class LeaveRequests extends Page implements Tables\Contracts\HasTable
{
    use Tables\Concerns\InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Pengajuan Cuti/Sakit/Lembur';

    protected static ?string $title = 'Pengajuan Saya';

    protected string $view = 'filament.employee.pages.leave-requests';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('submitRequest')
                ->label('Ajukan Pengajuan')
                ->icon('heroicon-o-plus-circle')
                ->color('success')
                ->modalHeading('Ajukan Pengajuan Baru')
                ->modalSubmitActionLabel('Kirim Pengajuan')
                ->form([
                    Select::make('type')
                        ->label('Jenis Pengajuan')
                        ->options(LeaveType::class)
                        ->required()
                        ->reactive(),
                    DatePicker::make('start_date')
                        ->label('Tanggal Mulai')
                        ->required(),
                    DatePicker::make('end_date')
                        ->label('Tanggal Selesai')
                        ->after('start_date'),
                    TimePicker::make('start_time')
                        ->label('Jam Mulai')
                        ->visible(fn ($get): bool => $get('type') === LeaveType::Overtime->value),
                    TimePicker::make('end_time')
                        ->label('Jam Selesai')
                        ->visible(fn ($get): bool => $get('type') === LeaveType::Overtime->value),
                    Textarea::make('reason')
                        ->label('Alasan')
                        ->rows(3)
                        ->columnSpanFull(),
                    FileUpload::make('attachment_path')
                        ->label('Lampiran (Surat Sakit, dll)')
                        ->disk('public')
                        ->directory('leave-attachments')
                        ->visibility('public')
                        ->acceptedFileTypes(['image/*', 'application/pdf'])
                        ->columnSpanFull(),
                ])
                ->action(function (array $data): void {
                    LeaveRequest::create([
                        ...$data,
                        'user_id' => auth()->id(),
                        'status' => 'pending',
                    ]);

                    Notification::make()
                        ->title('Pengajuan berhasil dikirim')
                        ->body('Menunggu persetujuan dari HR.')
                        ->success()
                        ->send();
                }),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(LeaveRequest::where('user_id', auth()->id()))
            ->columns([
                Tables\Columns\TextColumn::make('type')
                    ->label('Jenis')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => $state instanceof LeaveType ? $state->label() : ucfirst(str_replace('_', ' ', (string) $state)))
                    ->color(fn ($state): string => match ($state instanceof LeaveType ? $state : LeaveType::from((string) $state)) {
                        LeaveType::AnnualLeave => 'info',
                        LeaveType::SickLeave => 'warning',
                        LeaveType::Overtime => 'success',
                    }),
                Tables\Columns\TextColumn::make('start_date')
                    ->label('Mulai')
                    ->date('d M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_date')
                    ->label('Selesai')
                    ->date('d M Y'),
                Tables\Columns\TextColumn::make('reason')
                    ->label('Alasan')
                    ->limit(30)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => match ($state) {
                        'pending' => 'Menunggu',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                        default => ucfirst((string) $state),
                    })
                    ->color(fn ($state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('hr_notes')
                    ->label('Catatan HR')
                    ->limit(30)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal Pengajuan')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([10]);
    }
}
