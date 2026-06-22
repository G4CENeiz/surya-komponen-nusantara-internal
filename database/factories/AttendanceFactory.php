<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Attendance>
 */
class AttendanceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'employee_id' => Employee::factory(),
            'date' => fake()->dateTimeBetween('-1 month', 'now'),
            'clock_in' => fake()->time('08:00', '09:30'),
            'clock_out' => fake()->optional(0.8)->time('17:00', '18:30'),
            'status' => 'present',
            'clock_in_lat' => fake()->latitude(-6.3, -6.1),
            'clock_in_lng' => fake()->longitude(106.7, 106.9),
            'clock_out_lat' => null,
            'clock_out_lng' => null,
            'is_verified' => false,
            'verified_by' => null,
            'verified_at' => null,
            'notes' => null,
        ];
    }

    public function present(): static
    {
        return $this->state(fn () => [
            'status' => 'present',
            'clock_in' => fake()->time('07:30', '08:30'),
            'clock_out' => fake()->time('17:00', '18:00'),
            'clock_in_lat' => fake()->latitude(-6.3, -6.1),
            'clock_in_lng' => fake()->longitude(106.7, 106.9),
            'clock_out_lat' => fake()->latitude(-6.3, -6.1),
            'clock_out_lng' => fake()->longitude(106.7, 106.9),
        ]);
    }

    public function absent(): static
    {
        return $this->state(fn () => [
            'status' => 'absent',
            'clock_in' => null,
            'clock_out' => null,
        ]);
    }

    public function verified(): static
    {
        return $this->state(fn () => [
            'is_verified' => true,
            'verified_by' => User::factory(),
            'verified_at' => fake()->dateTimeBetween('-1 week', 'now'),
        ]);
    }
}
