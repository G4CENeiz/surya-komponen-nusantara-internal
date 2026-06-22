<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AnnouncementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $hr = \App\Models\User::where('email', 'hr@example.com')->first();
        if (!$hr) return;

        // Create a fake PDF attachment
        $pdfContent = '%PDF-1.4 %Fake PDF file for demo purposes';
        $attachmentPath = 'announcements/SOP_Baru_2026.pdf';
        \Illuminate\Support\Facades\Storage::disk('public')->put($attachmentPath, $pdfContent);

        \App\Models\Announcement::create([
            'type' => 'Penting',
            'title' => 'Pembaruan SOP Keamanan Pabrik',
            'content' => 'Kepada seluruh karyawan, terlampir adalah dokumen Standard Operating Procedure (SOP) Keamanan Pabrik terbaru yang wajib dibaca dan dipahami sebelum shift kerja dimulai.',
            'attachment_path' => $attachmentPath,
            'target' => 'all',
            'published_at' => now(),
            'expired_at' => now()->addDays(30),
            'is_active' => true,
            'created_by' => $hr->id,
        ]);
        
        \App\Models\Announcement::create([
            'type' => 'Informasi',
            'title' => 'Jadwal Libur Nasional Bulan Depan',
            'content' => 'Diberitahukan bahwa tanggal 17 Juli adalah libur nasional. Operasional kantor dan pabrik akan diliburkan secara serentak.',
            'attachment_path' => null,
            'target' => 'all',
            'published_at' => now()->subDays(2),
            'expired_at' => now()->addDays(14),
            'is_active' => true,
            'created_by' => $hr->id,
        ]);
    }
}
