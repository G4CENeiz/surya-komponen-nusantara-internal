<?php

namespace Database\Factories;

use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Department>
 */
class DepartmentFactory extends Factory
{
    protected static array $code = [];

    public function definition(): array
    {
        $code = fake()->unique()->bothify('??-###');
        static::$code[] = $code;

        return [
            'name' => fake()->unique()->words(2, true).' Department',
            'code' => strtoupper($code),
            'description' => fake()->sentence(),
        ];
    }
}
