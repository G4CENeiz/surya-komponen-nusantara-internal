<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\AttendanceCorrection;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AttendanceCorrection>
 */
class AttendanceCorrectionFactory extends Factory
{
    public function definition(): array
    {
        $oldData = [
            'clock_in' => '09:00:00',
            'clock_out' => null,
            'status' => 'absent',
        ];
        $newData = [
            'clock_in' => '08:00:00',
            'clock_out' => '17:00:00',
            'status' => 'present',
        ];

        return [
            'attendance_id' => Attendance::factory(),
            'corrected_by' => User::factory(),
            'correction_reason' => fake()->sentence(),
            'old_data' => $oldData,
            'new_data' => $newData,
        ];
    }
}
