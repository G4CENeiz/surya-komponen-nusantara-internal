<?php

namespace App\Filament\Employee\Pages;

use Filament\Pages\Page;

class Information extends Page
{
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-megaphone';

    protected static ?string $navigationLabel = 'Informasi';

    protected static ?string $title = 'Pusat Informasi';

    protected string $view = 'filament.employee.pages.information';

    public array $pengumumanList = [];
    public array $penugasanList = [];

    public function mount()
    {
        // Dummy data for Pengumuman (Announcements)
        $this->pengumumanList = [
            [
                'id' => 1,
                'title' => 'Pembaruan Kebijakan Cuti Tahunan',
                'date' => '2026-06-20',
                'priority' => 'Penting',
                'content' => 'Mulai tanggal 1 Juli 2026, pengajuan cuti tahunan harus dilakukan minimal 7 hari kerja sebelum tanggal pelaksanaan. Ketentuan ini berlaku untuk seluruh divisi pabrik maupun kantor. Kebijakan ini diambil untuk memastikan bahwa operasional perusahaan tetap berjalan lancar dan pengaturan jadwal pengganti dapat dilakukan dengan lebih matang. Selain itu, karyawan diwajibkan untuk memastikan bahwa seluruh tanggung jawab mendesak telah diserahterimakan kepada rekan kerja satu divisi sebelum mulai mengambil cuti. HRD akan menolak pengajuan cuti yang mendadak tanpa alasan darurat medis atau kedukaan.',
            ],
            [
                'id' => 2,
                'title' => 'Jadwal Pemeliharaan Sistem HRIS',
                'date' => '2026-06-18',
                'priority' => 'Informasi',
                'content' => 'Akan dilakukan pemeliharaan sistem HRIS pada hari Minggu, 28 Juni 2026 pukul 00:00 - 04:00 WIB. Sistem absensi tidak akan terganggu, namun menu pengajuan akan dinonaktifkan sementara.',
            ],
            [
                'id' => 3,
                'title' => 'Vaksinasi Influenza Tahunan',
                'date' => '2026-06-15',
                'priority' => 'Informasi',
                'content' => 'Pendaftaran vaksinasi influenza gratis bagi karyawan tetap dibuka mulai hari ini. Silakan mendaftar melalui klinik perusahaan paling lambat 30 Juni 2026.',
            ],
        ];

        // Dummy data for Penugasan (Tugas Luar)
        $this->penugasanList = [
            [
                'id' => 'TL-001',
                'title' => 'Kunjungan Klien VIP',
                'location' => 'Jakarta Selatan',
                'start_date' => '2026-06-25',
                'end_date' => '2026-06-27',
                'desc' => 'Presentasi produk terbaru dan negosiasi perpanjangan kontrak dengan PT Maju Jaya.',
                'status' => 'Belum Mulai',
            ],
            [
                'id' => 'TL-002',
                'title' => 'Audit Pabrik Cabang',
                'location' => 'Surabaya, Jawa Timur',
                'start_date' => '2026-06-15',
                'end_date' => '2026-06-18',
                'desc' => 'Melakukan inspeksi kualitas produksi dan audit kepatuhan K3 di fasilitas pabrik Surabaya.',
                'status' => 'Selesai',
            ],
            [
                'id' => 'TL-003',
                'title' => 'Pameran Otomotif Nasional',
                'location' => 'ICE BSD, Tangerang',
                'start_date' => '2026-06-20',
                'end_date' => '2026-06-24',
                'desc' => 'Menjaga booth pameran perusahaan, mendemonstrasikan komponen elektronik, dan mencari calon mitra bisnis baru.',
                'status' => 'Dalam Pengerjaan',
            ],
        ];

        // Urutkan Penugasan dari yang paling baru ke paling lama berdasarkan start_date
        usort($this->penugasanList, function ($a, $b) {
            return strtotime($b['start_date']) - strtotime($a['start_date']);
        });
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
}
