<?php

namespace App\Filament\Employee\Pages;

use Filament\Pages\Page;

class Information extends Page
{
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-megaphone';

    protected static ?string $navigationLabel = 'Informasi';
    protected static ?int $navigationSort = 3;

    protected static ?string $title = 'Pusat Informasi';

    protected static string $routePath = '/';

    protected string $view = 'filament.employee.pages.information';

    public array $pengumumanList = [];
    public array $penugasanList = [];

    public function mount()
    {
        $userId = auth()->id();

        // Mengambil data Pengumuman dari Database
        $this->pengumumanList = \App\Models\Announcement::active()
            ->published()
            ->latest('published_at')
            ->get()
            ->map(function ($ann) {
                return [
                    'id' => $ann->id,
                    'title' => $ann->title,
                    'date' => $ann->published_at,
                    'priority' => $ann->type ?? 'Informasi',
                    'content' => $ann->content,
                    'attachment_path' => $ann->attachment_path,
                ];
            })->toArray();

        // Mengambil data Penugasan dari Database
        $this->penugasanList = \App\Models\Assignment::where('assigned_to', $userId)
            ->where('is_active', true)
            ->orderBy('start_date', 'desc')
            ->get()
            ->map(function ($task) use ($userId) {
                $now = now();
                $status = 'Belum Mulai';
                if ($task->start_date && $task->end_date) {
                    if ($now->between($task->start_date, $task->end_date)) {
                        $status = 'Dalam Pengerjaan';
                    } elseif ($now->isAfter($task->end_date)) {
                        $status = 'Selesai';
                    }
                }

                $reimburse = \App\Models\Reimbursement::where('assignment_id', $task->id)
                    ->where('user_id', $userId)
                    ->first();

                return [
                    'id' => $task->id,
                    'title' => $task->title,
                    'location' => $task->notes ?? 'Kantor Pusat',
                    'start_date' => $task->start_date,
                    'end_date' => $task->end_date,
                    'desc' => $task->description,
                    'status' => $status,
                    'reimburse_status' => $reimburse ? $reimburse->status : null,
                    'reimburse_reason' => $reimburse ? $reimburse->rejection_reason : null,
                ];
            })->toArray();
    }

    public function viewPengumumanAction(): \Filament\Actions\Action
    {
        return \Filament\Actions\Action::make('viewPengumuman')
            ->modalHeading(fn (array $arguments) => collect($this->pengumumanList)->firstWhere('id', $arguments['id'])['title'] ?? 'Pengumuman')
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Tutup')
            ->modalContent(fn (array $arguments) => view('filament.employee.partials.information-modal', [
                'pengumuman' => collect($this->pengumumanList)->firstWhere('id', $arguments['id'])
            ]));
    }

    public function requestReimburseAction(): \Filament\Actions\Action
    {
        return \Filament\Actions\Action::make('requestReimburse')
            ->label('Reimburse')
            ->modalHeading('Form Reimbursement')
            ->modalDescription('Silakan unggah bukti kuitansi dan masukkan nominal reimbursement untuk tugas ini.')
            ->form([
                \Filament\Forms\Components\TextInput::make('amount')
                    ->label('Nominal Reimburse (Rp)')
                    ->numeric()
                    ->required()
                    ->minValue(100),
                \Filament\Forms\Components\Textarea::make('notes')
                    ->label('Keterangan / Tujuan')
                    ->required()
                    ->maxLength(500),
                \Filament\Forms\Components\FileUpload::make('attachment')
                    ->label('Bukti Kuitansi (PDF Wajib)')
                    ->acceptedFileTypes(['application/pdf'])
                    ->required()
                    ->maxSize(5120)
                    ->storeFiles(false)
                    ->helperText('Maks 5MB. Hanya format PDF.'),
            ])
            ->action(function (array $data, array $arguments) {
                $reimbursement = \App\Models\Reimbursement::create([
                    'user_id' => auth()->id(),
                    'assignment_id' => $arguments['id'],
                    'amount' => $data['amount'],
                    'notes' => $data['notes'],
                    'attachment_path' => 'spatie', // filler for NOT NULL constraint
                    'status' => 'pending',
                ]);

                if (!empty($data['attachment'])) {
                    $file = is_array($data['attachment']) ? current($data['attachment']) : $data['attachment'];
                    $reimbursement->addMedia($file->getRealPath())
                                  ->usingName($file->getClientOriginalName())
                                  ->usingFileName($file->getClientOriginalName())
                                  ->toMediaCollection('kuitansi');
                }

                \Filament\Notifications\Notification::make()
                    ->title('Reimbursement Diajukan')
                    ->body('Permintaan reimburse Anda telah dikirim ke Accounting untuk ditinjau.')
                    ->success()
                    ->send();

                // Refresh data
                $this->mount();
            });
    }
}
