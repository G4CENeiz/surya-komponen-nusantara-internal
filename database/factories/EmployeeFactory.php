<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use App\Models\Workplace;
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

        $faker = \Faker\Factory::create('id_ID');

        // Weighted department: 70% Produksi
        $departments = ['Produksi', 'HRD', 'Keuangan', 'IT', 'Gudang', 'Operasional'];
        $departmentWeights = [70, 5, 5, 5, 5, 10];
        $departmentIndex = $this->weightedRandom($departmentWeights);
        $departmentName = $departments[$departmentIndex];

        // Weighted status: 90% active
        $status = $faker->optional(0.9, 'inactive')->randomElement(['active']);

        // Realistic coordinates around Jakarta/Surabaya
        $locations = [
            ['lat' => -6.2088, 'lng' => 106.8456], // Jakarta
            ['lat' => -6.1751, 'lng' => 106.8650], // Jakarta Selatan
            ['lat' => -6.9175, 'lng' => 107.6191], // Bandung
            ['lat' => -7.2575, 'lng' => 112.7521], // Surabaya
            ['lat' => -6.5944, 'lng' => 106.7892], // Bogor
        ];
        $location = $faker->randomElement($locations);

        return [
            'user_id' => User::factory(),
            'nik' => $faker->numerify('################'), // 16-digit NIK
            'full_name' => $faker->name(),
            'place_of_birth' => $faker->city(),
            'date_of_birth' => $faker->dateTimeBetween('-40 years', '-22 years'),
            'gender' => $faker->randomElement(['male', 'female']),
            'phone' => $faker->phoneNumber(),
            'address' => $faker->address(),
            'office_email' => $faker->safeEmail(),
            'department_id' => Department::where('name', $departmentName)->first()->id ?? Department::factory(),
            'job_class_id' => $faker->numberBetween(1, 5),
            'workplace_id' => Workplace::inRandomOrder()->first()->id ?? Workplace::factory(),
            'hire_date' => $faker->dateTimeBetween('-5 years', 'now'),
            'termination_date' => $status === 'inactive' ? $faker->dateTimeBetween('-1 year', 'now') : null,
            'status' => $status,
            'face_photo_path' => 'face-references/dummy-face.jpg',
            'base_salary' => $faker->numberBetween(3000000, 15000000),
        ];
    }

    private function weightedRandom(array $weights): int
    {
        $total = array_sum($weights);
        $random = mt_rand(1, $total);
        $cumulative = 0;

        foreach ($weights as $index => $weight) {
            $cumulative += $weight;
            if ($random <= $cumulative) {
                return $index;
            }
        }

        return count($weights) - 1;
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
