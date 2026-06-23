<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Overtime;
use App\Models\TimeOff;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class PayrollTestDataSeeder extends Seeder
{
    public function run(): void
    {
        $month = now()->month;
        $year = now()->year;

        // Clean up previous test data for this month
        Attendance::whereMonth('date', $month)->whereYear('date', $year)->delete();
        Overtime::whereMonth('date', $month)->whereYear('date', $year)->delete();
        TimeOff::whereMonth('start_date', $month)->whereYear('start_date', $year)->delete();

        // Get active employees linked to users
        $employees = Employee::where('status', 'active')
            ->whereNotNull('user_id')
            ->with('user')
            ->inRandomOrder()
            ->take(300)
            ->get();

        $clockedIn = 0;
        $overtimeCount = 0;
        $timeOffCount = 0;

        foreach ($employees as $emp) {
            $userId = $emp->user_id;

            // Also create clock-in for TODAY (15% chance) so dashboard has live data
            if (rand(1, 100) <= 15) {
                $clockedIn++;
                Attendance::updateOrCreate(
                    ['user_id' => $userId, 'date' => now()->toDateString()],
                    [
                        'workplace_id' => $emp->workplace_id ?? 1,
                        'clock_in_at' => now()->subMinutes(rand(5, 60)),
                        'clock_in_lat' => -6.2088 + (mt_rand(-50, 50) / 1000),
                        'clock_in_lng' => 106.8456 + (mt_rand(-50, 50) / 1000),
                        'clock_in_ip' => '192.168.1.'.rand(1, 254),
                        'clock_in_within_geofence' => rand(1, 100) <= 70,
                        'clock_in_method' => rand(1, 100) <= 70 ? 'geofence' : 'manual',
                        'is_late' => now()->format('H:i') > '08:00',
                        'status' => 'pending_hr',
                    ]
                );
            }

            // LATE (40% chance)
            if (rand(1, 100) <= 40) {
                $lateDaysCount = rand(1, 5);
                for ($i = 1; $i <= $lateDaysCount; $i++) {
                    $day = rand(1, 28);
                    $date = Carbon::createFromDate($year, $month, $day)->toDateString();
                    if ($date === now()->toDateString()) {
                        continue; // Skip today, already handled above
                    }
                    Attendance::updateOrCreate(
                        ['user_id' => $userId, 'date' => $date],
                        [
                            'workplace_id' => $emp->workplace_id ?? 1,
                            'clock_in_at' => Carbon::createFromDate($year, $month, $day)->setTime(rand(8, 10), rand(5, 59)),
                            'clock_in_lat' => -6.2088 + (mt_rand(-50, 50) / 1000),
                            'clock_in_lng' => 106.8456 + (mt_rand(-50, 50) / 1000),
                            'clock_in_ip' => '192.168.1.'.rand(1, 254),
                            'clock_in_within_geofence' => true,
                            'clock_in_method' => 'geofence',
                            'is_late' => true,
                            'status' => 'approved',
                            // Add clock out too
                            'clock_out_at' => Carbon::createFromDate($year, $month, $day)->setTime(rand(16, 17), rand(0, 59)),
                            'clock_out_lat' => -6.2088 + (mt_rand(-50, 50) / 1000),
                            'clock_out_lng' => 106.8456 + (mt_rand(-50, 50) / 1000),
                            'clock_out_ip' => '192.168.1.'.rand(1, 254),
                            'clock_out_within_geofence' => true,
                            'clock_out_method' => 'geofence',
                        ]
                    );
                }
            }

            // NORMAL ATTENDANCE (always-on days to fill history)
            $normalDays = rand(3, 10);
            for ($i = 1; $i <= $normalDays; $i++) {
                $day = rand(1, 28);
                $date = Carbon::createFromDate($year, $month, $day)->toDateString();
                if ($date === now()->toDateString()) {
                    continue;
                }
                Attendance::updateOrCreate(
                    ['user_id' => $userId, 'date' => $date],
                    [
                        'workplace_id' => $emp->workplace_id ?? 1,
                        'clock_in_at' => Carbon::createFromDate($year, $month, $day)->setTime(rand(7, 8), rand(0, 59)),
                        'clock_in_lat' => -6.2088 + (mt_rand(-50, 50) / 1000),
                        'clock_in_lng' => 106.8456 + (mt_rand(-50, 50) / 1000),
                        'clock_in_ip' => '192.168.1.'.rand(1, 254),
                        'clock_in_within_geofence' => true,
                        'clock_in_method' => 'geofence',
                        'is_late' => false,
                        'status' => 'approved',
                        'clock_out_at' => Carbon::createFromDate($year, $month, $day)->setTime(rand(16, 17), rand(0, 59)),
                        'clock_out_lat' => -6.2088 + (mt_rand(-50, 50) / 1000),
                        'clock_out_lng' => 106.8456 + (mt_rand(-50, 50) / 1000),
                        'clock_out_ip' => '192.168.1.'.rand(1, 254),
                        'clock_out_within_geofence' => true,
                        'clock_out_method' => 'geofence',
                        'worked_hours' => rand(7, 9),
                    ]
                );
            }

            // OVERTIME (50% chance)
            if (rand(1, 100) <= 50) {
                $otDays = rand(1, 4);
                for ($i = 1; $i <= $otDays; $i++) {
                    $day = rand(1, 28);
                    $duration = rand(1, 4) * 60;
                    $startHour = 17;
                    $endHour = 17 + ($duration / 60);

                    Overtime::updateOrCreate(
                        ['user_id' => $userId, 'date' => Carbon::createFromDate($year, $month, $day)],
                        [
                            'start_time' => sprintf('%02d:00:00', $startHour),
                            'end_time' => sprintf('%02d:00:00', $endHour),
                            'duration_minutes' => $duration,
                            'status' => 'approved',
                            'reason' => 'Kerja tambahan project '.rand(100, 999),
                        ]
                    );
                    $overtimeCount++;
                }
            }

            // TIME OFF / CUTI (10% chance)
            if (rand(1, 100) <= 10) {
                $startDay = rand(1, 20);
                $endDay = $startDay + rand(1, 3);
                TimeOff::create([
                    'user_id' => $userId,
                    'start_date' => Carbon::createFromDate($year, $month, $startDay),
                    'end_date' => Carbon::createFromDate($year, $month, $endDay),
                    'type' => 'annual_leave',
                    'status' => 'approved',
                    'reason' => 'Cuti tahunan',
                ]);
                $timeOffCount++;
            }
        }

        $this->command->info('Seeded test data:');
        $this->command->info("  - Today's clock-ins: {$clockedIn}");
        $this->command->info('  - Attendance records: '.Attendance::whereMonth('date', $month)->whereYear('date', $year)->count());
        $this->command->info("  - Overtime records: {$overtimeCount}");
        $this->command->info("  - Time-off records: {$timeOffCount}");
    }
}
