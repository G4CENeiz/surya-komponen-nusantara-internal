<?php

namespace Database\Seeders;

use App\Models\Announcement;
use App\Models\Assignment;
use App\Models\Attendance;
use App\Models\JobClass;
use App\Models\LeaveRequest;
use App\Models\Office;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
        ]);

        // Create offices
        $officeJakarta = Office::create([
            'name' => 'Main Office - Jakarta',
            'latitude' => -6.2088,
            'longitude' => 106.8456,
            'radius_meters' => 100,
            'work_start' => '08:00:00',
            'work_end' => '17:00:00',
        ]);

        $officeBandung = Office::create([
            'name' => 'Branch Office - Bandung',
            'latitude' => -6.9175,
            'longitude' => 107.6191,
            'radius_meters' => 150,
            'work_start' => '08:30:00',
            'work_end' => '17:30:00',
        ]);

        // Create job classes (Kelas Jabatan)
        $jcStaff = JobClass::create(['name' => 'Staff', 'base_salary' => 5000000, 'allowance' => 500000, 'description' => 'Staff level']);
        $jcSenior = JobClass::create(['name' => 'Senior Staff', 'base_salary' => 8000000, 'allowance' => 1000000, 'description' => 'Senior staff level']);
        $jcSupervisor = JobClass::create(['name' => 'Supervisor', 'base_salary' => 12000000, 'allowance' => 2000000, 'description' => 'Supervisor level']);
        $jcManager = JobClass::create(['name' => 'Manager', 'base_salary' => 18000000, 'allowance' => 3000000, 'description' => 'Manager level']);
        $jcOperator = JobClass::create(['name' => 'Operator Produksi', 'base_salary' => 4500000, 'allowance' => 400000, 'description' => 'Production operator']);

        // Create HR user
        $hr = User::factory()->create([
            'name' => 'HR User',
            'email' => 'hr@example.com',
            'nik' => 'HR001',
            'department' => 'Human Resources',
            'job_class_id' => $jcManager->id,
            'office_id' => $officeJakarta->id,
        ]);
        $hr->assignRole('hr');

        // Create Accounting user
        $accounting = User::factory()->create([
            'name' => 'Accounting User',
            'email' => 'accounting@example.com',
            'nik' => 'ACC001',
            'department' => 'Finance',
            'job_class_id' => $jcManager->id,
            'office_id' => $officeJakarta->id,
        ]);
        $accounting->assignRole('accounting');

        // Create employee users
        $employee = User::factory()->create([
            'name' => 'Employee User',
            'email' => 'employee@example.com',
            'nik' => 'EMP001',
            'department' => 'Produksi',
            'job_class_id' => $jcStaff->id,
            'office_id' => $officeJakarta->id,
        ]);
        $employee->assignRole('employee');

        $employees = collect([$employee]);
        $departments = ['Produksi', 'Quality Control', 'Gudang', 'Maintenance', 'Admin'];
        $jobClasses = [$jcStaff, $jcSenior, $jcSupervisor, $jcOperator];

        for ($i = 2; $i <= 15; $i++) {
            $emp = User::factory()->create([
                'name' => fake()->name(),
                'email' => "emp{$i}@example.com",
                'nik' => 'EMP'.str_pad($i, 3, '0', STR_PAD_LEFT),
                'department' => $departments[array_rand($departments)],
                'job_class_id' => $jobClasses[array_rand($jobClasses)]->id,
                'office_id' => $i <= 10 ? $officeJakarta->id : $officeBandung->id,
            ]);
            $emp->assignRole('employee');
            $employees->push($emp);
        }

        // Create attendance records for the past 7 days
        $today = Carbon::today();
        foreach ($employees as $emp) {
            for ($day = 6; $day >= 0; $day--) {
                $date = $today->copy()->subDays($day);
                if ($date->isWeekend()) {
                    continue;
                }

                $isLate = rand(0, 100) < 20; // 20% chance of being late
                $clockInTime = $isLate
                    ? $date->copy()->setTime(8, rand(5, 45))
                    : $date->copy()->setTime(rand(7, 7), rand(30, 59));

                $hasClockOut = $day > 0; // Today might not have clock-out yet
                $clockOutTime = $hasClockOut
                    ? $date->copy()->setTime(rand(17, 19), rand(0, 59))
                    : null;

                $office = $emp->office_id === $officeJakarta->id ? $officeJakarta : $officeBandung;
                $lat = $office->latitude + (rand(-50, 50) / 100000);
                $lng = $office->longitude + (rand(-50, 50) / 100000);

                $workedHours = $hasClockOut
                    ? $clockInTime->diffInMinutes($clockOutTime) / 60
                    : null;

                Attendance::create([
                    'user_id' => $emp->id,
                    'office_id' => $office->id,
                    'date' => $date->toDateString(),
                    'clock_in_at' => $clockInTime,
                    'clock_in_lat' => $lat,
                    'clock_in_lng' => $lng,
                    'clock_in_ip' => '192.168.1.'.rand(1, 254),
                    'clock_in_face_confidence' => rand(70, 99) / 100,
                    'clock_in_within_geofence' => true,
                    'clock_in_method' => 'geofence',
                    'clock_out_at' => $clockOutTime,
                    'clock_out_lat' => $hasClockOut ? $lat : null,
                    'clock_out_lng' => $hasClockOut ? $lng : null,
                    'clock_out_ip' => $hasClockOut ? '192.168.1.'.rand(1, 254) : null,
                    'clock_out_face_confidence' => $hasClockOut ? rand(70, 99) / 100 : null,
                    'clock_out_within_geofence' => $hasClockOut ? true : null,
                    'clock_out_method' => $hasClockOut ? 'geofence' : null,
                    'status' => $day > 2 ? 'approved' : 'pending_hr',
                    'worked_hours' => $workedHours ? round($workedHours, 2) : null,
                    'is_late' => $isLate,
                    'is_early_leave' => $hasClockOut && $clockOutTime->hour < 17,
                ]);
            }
        }

        // Flag a couple of attendances as suspicious
        Attendance::where('user_id', $employees->random()->id)
            ->where('date', $today->copy()->subDays(3)->toDateString())
            ->update([
                'is_suspicious' => true,
                'suspicious_reason' => 'Foto wajah tidak sesuai dengan data referensi. Kemungkinan menggunakan foto orang lain.',
            ]);

        // Create leave requests
        LeaveRequest::create([
            'user_id' => $employees->get(1)->id,
            'type' => 'annual_leave',
            'start_date' => $today->copy()->addDays(5)->toDateString(),
            'end_date' => $today->copy()->addDays(7)->toDateString(),
            'reason' => 'Liburan keluarga ke Bali',
            'status' => 'pending',
        ]);

        LeaveRequest::create([
            'user_id' => $employees->get(2)->id,
            'type' => 'sick_leave',
            'start_date' => $today->copy()->subDays(1)->toDateString(),
            'end_date' => $today->copy()->addDays(1)->toDateString(),
            'reason' => 'Demam dan flu berat',
            'status' => 'approved',
            'reviewed_by' => $hr->id,
            'reviewed_at' => now(),
        ]);

        LeaveRequest::create([
            'user_id' => $employees->get(3)->id,
            'type' => 'overtime',
            'start_date' => $today->copy()->subDays(2)->toDateString(),
            'start_time' => '17:00',
            'end_time' => '21:00',
            'reason' => 'Menyelesaikan deadline project',
            'status' => 'approved',
            'reviewed_by' => $hr->id,
            'reviewed_at' => now(),
        ]);

        LeaveRequest::create([
            'user_id' => $employees->get(4)->id,
            'type' => 'annual_leave',
            'start_date' => $today->copy()->addDays(10)->toDateString(),
            'end_date' => $today->copy()->addDays(12)->toDateString(),
            'reason' => 'Acara keluarga',
            'status' => 'pending',
        ]);

        // Create announcements
        Announcement::create([
            'title' => 'Pengumuman Libur Nasional',
            'body' => 'Dengan ini diberitahukan bahwa kantor akan libur pada tanggal 17 Agustus dalam rangka HUT Kemerdekaan RI. Seluruh karyawan diharapkan kembali bekerja pada hari berikutnya.',
            'created_by' => $hr->id,
            'published_at' => now()->subDays(3),
        ]);

        Announcement::create([
            'title' => 'Perubahan Jam Kerja',
            'body' => 'Mulai bulan depan, jam kerja kantor berubah menjadi 08.00 - 17.00 WIB. Mohon perhatian seluruh karyawan.',
            'created_by' => $hr->id,
            'published_at' => now()->subDay(),
        ]);

        Announcement::create([
            'title' => 'Pelatihan K3',
            'body' => 'Akan diadakan pelatihan Keselamatan dan Kesehatan Kerja (K3) untuk seluruh karyawan produksi. Jadwal menyusul.',
            'created_by' => $hr->id,
            'published_at' => now(),
        ]);

        // Create assignments
        Assignment::create([
            'user_id' => $employees->first()->id,
            'title' => 'Laporan Bulanan Produksi',
            'description' => 'Buat laporan hasil produksi bulan ini dan kumpulkan ke supervisor.',
            'due_date' => $today->copy()->addDays(3)->toDateString(),
            'status' => 'in_progress',
            'created_by' => $hr->id,
        ]);

        Assignment::create([
            'user_id' => $employees->get(1)->id,
            'title' => 'Inspeksi Gudang',
            'description' => 'Lakukan inspeksi stok gudang A dan laporkan hasilnya.',
            'due_date' => $today->copy()->addDays(5)->toDateString(),
            'status' => 'pending',
            'created_by' => $hr->id,
        ]);

        Assignment::create([
            'user_id' => $employees->get(2)->id,
            'title' => 'Update SOP Produksi',
            'description' => 'Revisi SOP produksi lini 3 sesuai standar terbaru.',
            'due_date' => $today->copy()->subDay()->toDateString(),
            'status' => 'completed',
            'created_by' => $hr->id,
        ]);
    }
}
