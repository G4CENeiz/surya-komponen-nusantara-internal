<?php

namespace App\Filament\Employee\Pages;

use App\Enums\LeaveStatus;
use App\Enums\LeaveType;
use App\Models\LeaveRequest;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;

class Submission extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-plus';

    protected static ?string $navigationLabel = 'Pengajuan';

    protected static ?int $navigationSort = 2;

    protected static ?string $title = 'Pengajuan Karyawan';

    protected string $view = 'filament.employee.pages.submission';

    public $searchQuery = '';

    public ?array $filterData = [];

    public function mount()
    {
        $this->form->fill();
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Select::make('type')
                    ->options([
                        'annual_leave' => 'Cuti Tahunan',
                        'sick_leave' => 'Sakit',
                        'overtime' => 'Lembur',
                        'maternity_leave' => 'Cuti Melahirkan',
                        'marriage_leave' => 'Cuti Menikah',
                        'bereavement_leave' => 'Cuti Kematian Keluarga',
                        'personal_leave' => 'Cuti Pribadi',
                    ])
                    ->native(false)
                    ->live()
                    ->placeholder('Semua Jenis')
                    ->hiddenLabel()
                    ->extraAttributes(['class' => 'w-full sm:w-48']),

                Select::make('status')
                    ->options([
                        'pending' => 'Menunggu',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                    ])
                    ->native(false)
                    ->live()
                    ->placeholder('Semua Status')
                    ->hiddenLabel()
                    ->extraAttributes(['class' => 'w-full sm:w-48']),
            ])
            ->statePath('filterData')
            ->columns(2);
    }

    public function getPengajuanListProperty()
    {
        $userId = auth()->id();

        $requests = LeaveRequest::where('user_id', $userId)->get()->map(function ($lr) {
            $typeLabel = match ($lr->type) {
                LeaveType::AnnualLeave => 'Cuti Tahunan',
                LeaveType::SickLeave => 'Sakit',
                LeaveType::Overtime => 'Lembur',
                default => (string) $lr->type,
            };

            return [
                'id' => 'LR-'.str_pad($lr->id, 4, '0', STR_PAD_LEFT),
                'real_id' => $lr->id,
                'source' => 'leave_request',
                'date' => $lr->start_date ? $lr->start_date->format('Y-m-d') : null,
                'type' => $typeLabel,
                'type_value' => $lr->type instanceof LeaveType ? $lr->type->value : $lr->type,
                'desc' => $lr->reason,
                'status' => $lr->status instanceof LeaveStatus ? $lr->status->value : $lr->status,
                'attachment' => $lr->attachment_path,
                'hr_notes' => $lr->hr_notes,
            ];
        });

        if (! empty($this->searchQuery)) {
            $search = strtolower($this->searchQuery);
            $requests = $requests->filter(function ($item) use ($search) {
                return str_contains(strtolower($item['id']), $search) || str_contains(strtolower($item['desc']), $search);
            });
        }

        if (! empty($this->filterData['type'])) {
            $requests = $requests->filter(fn ($item) => $item['type_value'] === $this->filterData['type']);
        }

        if (! empty($this->filterData['status'])) {
            $requests = $requests->filter(fn ($item) => $item['status'] === $this->filterData['status']);
        }

        return $requests->sortByDesc('date')->values();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('createLeave')
                ->label('Buat Pengajuan Cuti')
                ->icon('heroicon-o-calendar-days')
                ->modalHeading('Formulir Pengajuan Cuti')
                ->modalSubmitActionLabel('Kirim Pengajuan')
                ->form([
                    Select::make('type')
                        ->label('Jenis Cuti')
                        ->required()
                        ->options([
                            'annual_leave' => 'Cuti Tahunan',
                            'maternity_leave' => 'Cuti Melahirkan',
                            'marriage_leave' => 'Cuti Menikah',
                            'bereavement_leave' => 'Cuti Kematian Keluarga',
                            'personal_leave' => 'Cuti Pribadi',
                        ])
                        ->native(false),
                    DatePicker::make('start_date')
                        ->label('Tanggal Mulai')
                        ->required(),
                    DatePicker::make('end_date')
                        ->label('Tanggal Selesai')
                        ->required()
                        ->afterOrEqual('start_date'),
                    Textarea::make('reason')
                        ->label('Alasan Cuti')
                        ->required()
                        ->rows(3),
                ])
                ->action(function (array $data) {
                    LeaveRequest::create([
                        'user_id' => auth()->id(),
                        'type' => LeaveType::AnnualLeave,
                        'start_date' => $data['start_date'],
                        'end_date' => $data['end_date'],
                        'reason' => $data['reason'],
                        'status' => LeaveStatus::Pending,
                    ]);

                    Notification::make()
                        ->title('Pengajuan Cuti Berhasil Dikirim')
                        ->success()
                        ->send();
                }),

            Action::make('createOvertime')
                ->label('Buat Pengajuan Lembur')
                ->icon('heroicon-o-clock')
                ->modalHeading('Formulir Pengajuan Lembur')
                ->modalSubmitActionLabel('Kirim Pengajuan')
                ->form([
                    DatePicker::make('start_date')
                        ->label('Tanggal Lembur')
                        ->required(),
                    TimePicker::make('start_time')
                        ->label('Jam Mulai')
                        ->required(),
                    TimePicker::make('end_time')
                        ->label('Jam Selesai')
                        ->required()
                        ->after('start_time'),
                    Textarea::make('reason')
                        ->label('Keterangan / Tugas')
                        ->required()
                        ->rows(3),
                ])
                ->action(function (array $data) {
                    LeaveRequest::create([
                        'user_id' => auth()->id(),
                        'type' => LeaveType::Overtime,
                        'start_date' => $data['start_date'],
                        'end_date' => $data['start_date'],
                        'start_time' => $data['start_time'],
                        'end_time' => $data['end_time'],
                        'reason' => $data['reason'],
                        'status' => LeaveStatus::Pending,
                    ]);

                    Notification::make()
                        ->title('Pengajuan Lembur Berhasil Dikirim')
                        ->success()
                        ->send();
                }),

            Action::make('createSickLeave')
                ->label('Buat Pengajuan Sakit')
                ->icon('heroicon-o-heart')
                ->color('danger')
                ->modalHeading('Formulir Pengajuan Sakit')
                ->modalSubmitActionLabel('Kirim Pengajuan')
                ->form([
                    DatePicker::make('start_date')
                        ->label('Tanggal Mulai Sakit')
                        ->required(),
                    DatePicker::make('end_date')
                        ->label('Tanggal Selesai Sakit')
                        ->required()
                        ->afterOrEqual('start_date'),
                    FileUpload::make('attachment_path')
                        ->label('Surat Keterangan Dokter')
                        ->required()
                        ->directory('sick_leaves')
                        ->acceptedFileTypes(['image/*', 'application/pdf'])
                        ->maxSize(5120),
                ])
                ->action(function (array $data) {
                    LeaveRequest::create([
                        'user_id' => auth()->id(),
                        'type' => LeaveType::SickLeave,
                        'start_date' => $data['start_date'],
                        'end_date' => $data['end_date'],
                        'reason' => 'Sakit',
                        'attachment_path' => $data['attachment_path'],
                        'status' => LeaveStatus::Pending,
                    ]);

                    Notification::make()
                        ->title('Pengajuan Sakit Berhasil Dikirim')
                        ->success()
                        ->send();
                }),
        ];
    }
}
