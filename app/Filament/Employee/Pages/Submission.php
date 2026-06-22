<?php

namespace App\Filament\Employee\Pages;

use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;

class Submission extends Page implements HasForms
{
    use InteractsWithForms;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-document-plus';

    protected static ?string $navigationLabel = 'Pengajuan';

    protected static ?string $title = 'Pengajuan Karyawan';

    protected string $view = 'filament.employee.pages.submission';

    public $searchQuery = '';
    public ?array $filterData = [];

    public function mount()
    {
        $this->form->fill();
    }

    public function form(\Filament\Schemas\Schema $form): \Filament\Schemas\Schema
    {
        return $form
            ->schema([
                \Filament\Forms\Components\Select::make('type')
                    ->options([
                        'Cuti Tahunan' => 'Cuti Tahunan',
                        'Lembur' => 'Lembur',
                        'Sakit' => 'Sakit',
                    ])
                    ->native(false)
                    ->live()
                    ->placeholder('Semua Jenis')
                    ->hiddenLabel()
                    ->extraAttributes(['class' => 'w-full sm:w-48']),

                \Filament\Forms\Components\Select::make('status')
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
        $userId = auth()->id() ?? 1; // Fallback for dev if not logged in

        $timeOffs = \App\Models\TimeOff::where('user_id', $userId)->get()->map(function($t) {
            return [
                'id' => 'TO-' . str_pad($t->id, 4, '0', STR_PAD_LEFT),
                'real_id' => $t->id,
                'source' => 'time_off',
                'date' => $t->start_date ? $t->start_date->format('Y-m-d') : null,
                'type' => $t->type,
                'desc' => $t->reason,
                'status' => $t->status,
                'attachment' => $t->attachment_path,
            ];
        });

        $overtimes = \App\Models\Overtime::where('user_id', $userId)->get()->map(function($o) {
            return [
                'id' => 'OT-' . str_pad($o->id, 4, '0', STR_PAD_LEFT),
                'real_id' => $o->id,
                'source' => 'overtime',
                'date' => $o->date ? $o->date->format('Y-m-d') : null,
                'type' => 'Lembur',
                'desc' => $o->reason,
                'status' => $o->status,
                'attachment' => null,
            ];
        });

        $all = $timeOffs->concat($overtimes);

        if (!empty($this->searchQuery)) {
            $search = strtolower($this->searchQuery);
            $all = $all->filter(function($item) use ($search) {
                return str_contains(strtolower($item['id']), $search) || str_contains(strtolower($item['desc']), $search);
            });
        }

        if (!empty($this->filterData['type'])) {
            $all = $all->filter(fn($item) => $item['type'] === $this->filterData['type']);
        }

        if (!empty($this->filterData['status'])) {
            $all = $all->filter(fn($item) => $item['status'] === $this->filterData['status']);
        }

        return $all->sortByDesc('date')->values();
    }

    public function createLeaveAction(): Action
    {
        return Action::make('createLeave')
            ->label('Buat Pengajuan Cuti')
            ->modalHeading('Formulir Pengajuan Cuti')
            ->modalSubmitActionLabel('Kirim Pengajuan')
            ->form([
                \Filament\Forms\Components\Select::make('type')
                    ->label('Jenis Cuti')
                    ->required()
                    ->options([
                        'Cuti Tahunan' => 'Tahunan',
                        'Cuti Melahirkan' => 'Melahirkan',
                        'Cuti Khusus' => 'Khusus',
                        'Lainnya' => 'Lainnya',
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
                \App\Models\TimeOff::create([
                    'user_id' => auth()->id() ?? 1,
                    'type' => $data['type'],
                    'start_date' => $data['start_date'],
                    'end_date' => $data['end_date'],
                    'reason' => $data['reason'],
                    'status' => 'pending',
                ]);

                Notification::make()
                    ->title('Pengajuan Cuti Berhasil Dikirim')
                    ->success()
                    ->send();
            });
    }

    public function createOvertimeAction(): Action
    {
        return Action::make('createOvertime')
            ->label('Buat Pengajuan Lembur')
            ->modalHeading('Formulir Pengajuan Lembur')
            ->modalSubmitActionLabel('Kirim Pengajuan')
            ->form([
                DatePicker::make('date')
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
                $start = \Carbon\Carbon::parse($data['start_time']);
                $end = \Carbon\Carbon::parse($data['end_time']);
                $duration = $end->diffInMinutes($start);

                \App\Models\Overtime::create([
                    'user_id' => auth()->id() ?? 1,
                    'date' => $data['date'],
                    'start_time' => $data['start_time'],
                    'end_time' => $data['end_time'],
                    'duration_minutes' => $duration,
                    'reason' => $data['reason'],
                    'status' => 'pending',
                ]);

                Notification::make()
                    ->title('Pengajuan Lembur Berhasil Dikirim')
                    ->success()
                    ->send();
            });
    }

    public function createSickLeaveAction(): Action
    {
        return Action::make('createSickLeave')
            ->label('Buat Pengajuan Sakit')
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
                \App\Models\TimeOff::create([
                    'user_id' => auth()->id() ?? 1,
                    'type' => 'Sakit',
                    'start_date' => $data['start_date'],
                    'end_date' => $data['end_date'],
                    'reason' => 'Sakit',
                    'attachment_path' => $data['attachment_path'],
                    'status' => 'pending',
                ]);

                Notification::make()
                    ->title('Pengajuan Sakit Berhasil Dikirim')
                    ->success()
                    ->send();
            });
    }
}
