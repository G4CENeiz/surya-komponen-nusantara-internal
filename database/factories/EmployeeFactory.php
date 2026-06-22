<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\Employee;
use App\Models\JobClass;
use App\Models\User;
use App\Models\WorkLocation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Employee>
 */
class EmployeeFactory extends Factory
{
    protected static int $nik = 0;

    public function definition(): array
    {
        static::$nik++;

        return [
            'user_id' => User::factory(),
            'nik' => 'NIK-'.str_pad(static::$nik, 5, '0', STR_PAD_LEFT),
            'full_name' => fake()->name(),
            'place_of_birth' => fake()->city(),
            'date_of_birth' => fake()->dateTimeBetween('-40 years', '-22 years'),
            'gender' => fake()->randomElement(['male', 'female']),
            'phone' => fake()->phoneNumber(),
            'address' => fake()->address(),
            'office_email' => fake()->safeEmail(),
            'department_id' => Department::factory(),
            'job_class_id' => JobClass::factory(),
            'work_location_id' => WorkLocation::factory(),
            'hire_date' => fake()->dateTimeBetween('-5 years', 'now'),
            'status' => 'active',
            'base_salary' => fake()->numberBetween(3000000, 15000000),
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => [
            'status' => 'inactive',
            'termination_date' => fake()->dateTimeBetween('-1 year', 'now'),
        ]);
    }

    public function onLeave(): static
    {
        return $this->state(fn () => [
            'status' => 'on_leave',
        ]);
    }

    public function sick(): static
    {
        return $this->state(fn () => [
            'status' => 'sick',
        ]);
    }
}
