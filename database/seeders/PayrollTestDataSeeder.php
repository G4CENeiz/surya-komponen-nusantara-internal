<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employee;
use App\Models\Attendance;
use App\Models\Overtime;
use App\Models\TimeOff;
use Carbon\Carbon;

class PayrollTestDataSeeder extends Seeder
{
    public function run()
    {
        $month = now()->month;
        $year = now()->year;

        // Clean up previous test data for this month
        Attendance::whereMonth('date', $month)->whereYear('date', $year)->delete();
        Overtime::whereMonth('date', $month)->whereYear('date', $year)->delete();
        TimeOff::whereMonth('start_date', $month)->whereYear('start_date', $year)->delete();

        // Get up to 300 active employees to make the table look rich with data
        $employees = Employee::where('status', 'active')->whereNotNull('user_id')->inRandomOrder()->take(300)->get();

        foreach ($employees as $index => $emp) {
            // Random chance: 40% have late attendance, 50% have overtime, 10% have time-off
            
            // LATE (40% chance)
            if (rand(1, 100) <= 40) {
                $lateDaysCount = rand(1, 5); // late 1 to 5 days
                for ($i = 1; $i <= $lateDaysCount; $i++) {
                    // Random date in the month
                    $day = rand(1, 28);
                    Attendance::updateOrCreate(
                        ['user_id' => $emp->user_id, 'date' => Carbon::createFromDate($year, $month, $day)],
                        [
                            'clock_in_at' => Carbon::createFromDate($year, $month, $day)->setTime(rand(9, 10), rand(15, 59)),
                            'is_late' => true,
                            'status' => 'approved',
                            'workplace_id' => 1
                        ]
                    );
                }
            }

            // OVERTIME (50% chance)
            if (rand(1, 100) <= 50) {
                $overtimeCount = rand(1, 4); // 1 to 4 overtime days
                for ($i = 1; $i <= $overtimeCount; $i++) {
                    $day = rand(1, 28);
                    $duration = rand(1, 4) * 60; // 60, 120, 180, or 240 minutes
                    $startHour = 17;
                    $endHour = 17 + ($duration / 60);

                    Overtime::updateOrCreate(
                        ['user_id' => $emp->user_id, 'date' => Carbon::createFromDate($year, $month, $day)],
                        [
                            'start_time' => sprintf('%02d:00:00', $startHour),
                            'end_time' => sprintf('%02d:00:00', $endHour),
                            'duration_minutes' => $duration,
                            'status' => 'approved',
                            'reason' => 'Kerja tambahan project ' . rand(100, 999)
                        ]
                    );
                }
            }

            // TIME OFF / CUTI (10% chance)
            if (rand(1, 100) <= 10) {
                $startDay = rand(1, 20);
                $endDay = $startDay + rand(1, 3);
                TimeOff::create([
                    'user_id' => $emp->user_id,
                    'start_date' => Carbon::createFromDate($year, $month, $startDay),
                    'end_date' => Carbon::createFromDate($year, $month, $endDay),
                    'type' => 'annual_leave',
                    'status' => 'approved',
                    'reason' => 'Cuti tahunan'
                ]);
            }
        }

        $this->command->info('Mass payroll test data seeded successfully for ' . $employees->count() . ' employees!');
    }
}
