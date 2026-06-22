<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AssignmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $employee = \App\Models\User::where('email', 'employee@example.com')->first();
        $hr = \App\Models\User::where('email', 'hr@example.com')->first();
        
        if (!$employee || !$hr) {
            return;
        }

        $assignments = [
            [
                'title' => 'Kunjungan Klien PT Maju Mundur',
                'description' => 'Melakukan pengecekan rutin dan pemeliharaan alat di lokasi klien.',
                'assigned_to' => $employee->id,
                'created_by' => $hr->id,
                'start_date' => \Carbon\Carbon::now()->subDays(9)->format('Y-m-d'), // June 14
                'end_date' => \Carbon\Carbon::now()->subDays(6)->format('Y-m-d'),   // June 17
                'notes' => 'Kantor PT Maju Mundur',
                'is_active' => false,
            ],
            [
                'title' => 'Instalasi Mesin Baru Cabang Bekasi',
                'description' => 'Membantu tim teknis untuk proses instalasi mesin produksi tahap 2.',
                'assigned_to' => $employee->id,
                'created_by' => $hr->id,
                'start_date' => \Carbon\Carbon::now()->subDays(3)->format('Y-m-d'), // June 20
                'end_date' => \Carbon\Carbon::now()->subDays(2)->format('Y-m-d'),   // June 21
                'notes' => 'Cabang Bekasi',
                'is_active' => false,
            ],
            [
                'title' => 'Audit Internal Gudang Utama',
                'description' => 'Melakukan stock opname bersama tim logistik pusat.',
                'assigned_to' => $employee->id,
                'created_by' => $hr->id,
                'start_date' => \Carbon\Carbon::now()->subDays(1)->format('Y-m-d'), // June 22
                'end_date' => \Carbon\Carbon::now()->addDays(2)->format('Y-m-d'),   // June 25
                'notes' => 'Gudang Utama Semarang',
                'is_active' => true,
            ],
            [
                'title' => 'Pelatihan K3 & ISO 9001',
                'description' => 'Wajib hadir di aula lantai 3 untuk pembaruan sertifikasi K3.',
                'assigned_to' => $employee->id,
                'created_by' => $hr->id,
                'start_date' => \Carbon\Carbon::now()->addDays(3)->format('Y-m-d'), // June 26
                'end_date' => \Carbon\Carbon::now()->addDays(5)->format('Y-m-d'),   // June 28
                'notes' => 'Aula Lantai 3',
                'is_active' => true,
            ],
        ];

        foreach ($assignments as $data) {
            \App\Models\Assignment::create($data);
        }
    }
}
