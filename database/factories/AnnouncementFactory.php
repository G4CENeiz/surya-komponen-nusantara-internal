<?php

namespace Database\Factories;

use App\Models\Announcement;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Announcement>
 */
class AnnouncementFactory extends Factory
{
    protected static int $counter = 0;

    public function definition(): array
    {
        static::$counter++;

        return [
            'type' => fake()->randomElement(['announcement', 'assignment']),
            'title' => fake()->sentence(4).' #'.static::$counter,
            'content' => fake()->paragraph(),
            'attachment_path' => null,
            'target' => fake()->randomElement(['all', 'specific']),
            'published_at' => fake()->dateTimeBetween('-1 month', 'now'),
            'expired_at' => fake()->optional(0.3)->dateTimeBetween('+1 week', '+3 months'),
            'is_active' => true,
            'created_by' => User::factory(),
        ];
    }

    public function announcement(): static
    {
        return $this->state(fn () => ['type' => 'announcement']);
    }

    public function assignment(): static
    {
        return $this->state(fn () => ['type' => 'assignment']);
    }
}
