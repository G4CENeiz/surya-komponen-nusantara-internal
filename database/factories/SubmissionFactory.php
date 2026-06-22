<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Submission>
 */
class SubmissionFactory extends Factory
{
    public function definition(): array
    {
        $startDate = fake()->dateTimeBetween('-2 months', 'now');

        return [
            'employee_id' => Employee::factory(),
            'type' => fake()->randomElement(['leave', 'sick', 'overtime']),
            'start_date' => $startDate,
            'end_date' => (clone $startDate)->modify('+'.fake()->numberBetween(0, 3).' days'),
            'reason' => fake()->sentence(),
            'doctor_letter_path' => null,
            'sick_notes' => null,
            'overtime_date' => null,
            'start_time' => null,
            'end_time' => null,
            'overtime_notes' => null,
            'total_hours' => null,
            'status' => 'pending',
            'reviewed_by' => null,
            'reviewed_at' => null,
            'review_notes' => null,
        ];
    }

    public function leave(): static
    {
        return $this->state(fn () => ['type' => 'leave']);
    }

    public function sick(): static
    {
        return $this->state(fn () => [
            'type' => 'sick',
            'doctor_letter_path' => 'uploads/doctor-letters/'.fake()->uuid().'.pdf',
        ]);
    }

    public function overtime(): static
    {
        $date = fake()->dateTimeBetween('-1 month', 'now');
        $startHour = fake()->numberBetween(17, 20);
        $endHour = $startHour + fake()->numberBetween(1, 4);

        return $this->state(fn () => [
            'type' => 'overtime',
            'start_date' => $date,
            'end_date' => $date,
            'overtime_date' => $date,
            'start_time' => sprintf('%02d:00:00', $startHour),
            'end_time' => sprintf('%02d:00:00', min($endHour, 23)),
            'total_hours' => $endHour - $startHour,
            'overtime_notes' => fake()->sentence(),
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn () => [
            'status' => 'pending',
            'reviewed_by' => null,
            'reviewed_at' => null,
        ]);
    }

    public function approved(): static
    {
        return $this->state(fn () => [
            'status' => 'approved',
            'reviewed_by' => User::factory(),
            'reviewed_at' => fake()->dateTimeBetween('-1 week', 'now'),
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn () => [
            'status' => 'rejected',
            'reviewed_by' => User::factory(),
            'reviewed_at' => fake()->dateTimeBetween('-1 week', 'now'),
            'review_notes' => fake()->sentence(),
        ]);
    }
}
