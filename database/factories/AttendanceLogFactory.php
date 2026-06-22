<?php

namespace Database\Factories;

use App\Models\Attendance;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceLogFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'attendance_id' => Attendance::factory(),
            'action' => fake()->word(),
            'old_value' => '{}',
            'new_value' => '{}',
            'ip_address' => fake()->word(),
            'user_agent' => fake()->text(),
            'metadata' => '{}',
        ];
    }
}
