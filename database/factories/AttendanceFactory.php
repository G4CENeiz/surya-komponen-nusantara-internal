<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Workplace;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'workplace_id' => Workplace::factory(),
            'date' => fake()->date(),
            'clock_in_at' => fake()->dateTime(),
            'clock_in_lat' => fake()->randomFloat(7, 0, 999.9999999),
            'clock_in_lng' => fake()->randomFloat(7, 0, 999.9999999),
            'clock_in_ip' => fake()->word(),
            'clock_in_photo_path' => fake()->word(),
            'clock_in_face_confidence' => fake()->randomFloat(4, 0, 9.9999),
            'clock_in_within_geofence' => fake()->boolean(),
            'clock_in_method' => fake()->word(),
            'clock_out_at' => fake()->dateTime(),
            'clock_out_lat' => fake()->randomFloat(7, 0, 999.9999999),
            'clock_out_lng' => fake()->randomFloat(7, 0, 999.9999999),
            'clock_out_ip' => fake()->word(),
            'clock_out_photo_path' => fake()->word(),
            'clock_out_face_confidence' => fake()->randomFloat(4, 0, 9.9999),
            'clock_out_within_geofence' => fake()->boolean(),
            'clock_out_method' => fake()->word(),
            'status' => fake()->word(),
            'hr_notes' => fake()->text(),
            'verified_by' => User::factory(),
            'verified_at' => fake()->dateTime(),
            'worked_hours' => fake()->randomFloat(2, 0, 999.99),
            'is_late' => fake()->boolean(),
            'is_early_leave' => fake()->boolean(),
        ];
    }
}
