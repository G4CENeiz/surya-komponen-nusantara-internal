<?php

namespace Database\Factories;

use App\Models\JobClass;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<JobClass>
 */
class JobClassFactory extends Factory
{
    protected static int $level = 0;

    public function definition(): array
    {
        static::$level++;

        $minSalary = fake()->numberBetween(3000000, 8000000);

        return [
            'name' => fake()->unique()->jobTitle(),
            'code' => 'JC-'.str_pad(static::$level, 3, '0', STR_PAD_LEFT),
            'level' => static::$level,
            'min_salary' => $minSalary,
            'max_salary' => $minSalary + fake()->numberBetween(2000000, 5000000),
            'base_allowance' => fake()->numberBetween(500000, 2000000),

            'description' => fake()->sentence(),
        ];
    }
}
