<?php

namespace Database\Factories;

use App\Models\WorkLocation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WorkLocation>
 */
class WorkLocationFactory extends Factory
{
    protected static int $counter = 0;

    public function definition(): array
    {
        static::$counter++;

        return [
            'name' => fake()->city().' Office '.static::$counter,
            'code' => 'LOC-'.str_pad(static::$counter, 3, '0', STR_PAD_LEFT),
            'address' => fake()->address(),
            'latitude' => fake()->latitude(-10, 10),
            'longitude' => fake()->longitude(100, 140),
            'radius_meters' => fake()->randomElement([50, 100, 150, 200]),
            'is_active' => true,
        ];
    }
}
