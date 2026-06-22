<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employee;
use App\Models\Attendance;
use App\Models\Overtime;
use App\Models\TimeOff;
use App\Models\Payslip;
use App\Models\PayrollSetting;
use Carbon\Carbon;

class HistoricalPayrollTestDataSeeder extends Seeder
{
    public function run()
    {
        $overtimeSetting = PayrollSetting::where('key', 'overtime_rate')->first();
        $overtimeRate = $overtimeSetting ? (int)$overtimeSetting->value : 50000;
        
        $lateSetting = PayrollSetting::where('key', 'late_penalty')->first();
        $latePenalty = $lateSetting ? (int)$lateSetting->value : 25000;

        $employees = Employee::where('status', 'active')->whereNotNull('user_id')->inRandomOrder()->get();

        // Loop for the past 3 months
        for ($m = 3; $m >= 1; $m--) {
            $date = now()->subMonthsNoOverflow($m);
            $month = $date->month;
            $year = $date->year;

            $this->command->info("Seeding data for $month / $year...");

            // Cleanup
            Attendance::whereMonth('date', $month)->whereYear('date', $year)->delete();
            Overtime::whereMonth('date', $month)->whereYear('date', $year)->delete();
            TimeOff::whereMonth('start_date', $month)->whereYear('start_date', $year)->delete();
            Payslip::where('period_month', $month)->where('period_year', $year)->delete();

            foreach ($employees as $emp) {
                $otHours = 0;
                $lateFreq = 0;
                
                // LATE (40% chance)
                if (rand(1, 100) <= 40) {
                    $lateFreq = rand(1, 4);
                    for ($i = 1; $i <= $lateFreq; $i++) {
                        $day = rand(1, 28);
                        Attendance::updateOrCreate(
                            ['employee_id' => $emp->id, 'date' => Carbon::createFromDate($year, $month, $day)],
                            [
                                'clock_in_at' => Carbon::createFromDate($year, $month, $day)->setTime(rand(9, 10), rand(15, 59)),
                                'is_late' => true,
                                'status' => 'approved',
                                'workplace_id' => 1,
                            ]
                        );
                    }
                }

                // OVERTIME (40% chance)
                if (rand(1, 100) <= 40) {
                    $overtimeCount = rand(1, 4);
                    for ($i = 1; $i <= $overtimeCount; $i++) {
                        $day = rand(1, 28);
                        $duration = rand(1, 4) * 60;
                        $otHours += ($duration / 60);

                        Overtime::updateOrCreate(
                            ['user_id' => $emp->user_id, 'date' => Carbon::createFromDate($year, $month, $day)],
                            [
                                'start_time' => '17:00:00',
                                'end_time' => sprintf('%02d:00:00', 17 + ($duration / 60)),
                                'duration_minutes' => $duration,
                                'status' => 'approved',
                                'reason' => 'Historical Lembur'
                            ]
                        );
                    }
                }

                // TIME OFF (10% chance)
                if (rand(1, 100) <= 10) {
                    $startDay = rand(1, 20);
                    $endDay = $startDay + rand(1, 3);
                    TimeOff::create([
                        'user_id' => $emp->user_id,
                        'start_date' => Carbon::createFromDate($year, $month, $startDay),
                        'end_date' => Carbon::createFromDate($year, $month, $endDay),
                        'type' => 'annual_leave',
                        'status' => 'approved',
                        'reason' => 'Historical Cuti'
                    ]);
                }

                // Calculate & Create Payslip
                $otPay = $otHours * $overtimeRate;
                $lateDeduction = $lateFreq * $latePenalty;
                $thp = $emp->base_salary + $otPay - $lateDeduction;

                Payslip::create([
                    'employee_id' => $emp->id,
                    'period_month' => $month,
                    'period_year' => $year,
                    'status' => 'paid',
                    'payment_date' => Carbon::createFromDate($year, $month, 25),
                    'base_salary' => $emp->base_salary,
                    'overtime_hours' => $otHours,
                    'overtime_pay' => $otPay,
                    'total_allowance' => $otPay,
                    'total_deduction' => $lateDeduction,
                    'net_salary' => $thp,
                    'components_detail' => [
                        'overtime_hours' => $otHours,
                        'late_frequency' => $lateFreq,
                        'ded_late' => $lateDeduction,
                    ]
                ]);
            }
        }
        $this->command->info('Historical Payroll test data seeded successfully for 3 past months!');
    }
}
