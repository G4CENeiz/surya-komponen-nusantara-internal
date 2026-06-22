<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\User;
use App\Models\Workplace;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Attendance>
 */
class AttendanceFactory extends Factory
{
    /**
     * Jakarta-area coordinates for realistic geofencing data.
     */
    private const JAKARTA_COORDS = [
        'lat' => [-6.2300, -6.2100, -6.1800, -6.2500, -6.1950],
        'lng' => [106.8100, 106.8200, 106.8350, 106.7950, 106.8250],
    ];

    public function definition(): array
    {
        $latIndex = array_rand(self::JAKARTA_COORDS['lat']);
        $clockInTime = fake()->dateTimeBetween('-12 hours', '-4 hours');
        $clockOutTime = (clone $clockInTime)->modify('+'.random_int(4, 9).' hours');

        return [
            'employee_id' => Employee::factory(),
            'workplace_id' => Workplace::factory(),
            'date' => fake()->date(),
            'clock_in' => $clockInTime,
            'clock_in_at' => $clockInTime,
            'clock_in_lat' => self::JAKARTA_COORDS['lat'][$latIndex],
            'clock_in_lng' => self::JAKARTA_COORDS['lng'][$latIndex],
            'clock_in_ip' => fake()->ipv4(),
            'clock_in_photo_path' => null,
            'clock_in_within_geofence' => fake()->boolean(85),
            'clock_in_method' => fake()->randomElement(['geofence', 'manual', 'face_recognition']),
            'clock_out' => $clockOutTime,
            'clock_out_at' => $clockOutTime,
            'clock_out_lat' => self::JAKARTA_COORDS['lat'][$latIndex] + fake()->randomFloat(6, -0.001, 0.001),
            'clock_out_lng' => self::JAKARTA_COORDS['lng'][$latIndex] + fake()->randomFloat(6, -0.001, 0.001),
            'clock_out_ip' => fake()->ipv4(),
            'clock_out_photo_path' => null,
            'clock_out_within_geofence' => fake()->boolean(85),
            'clock_out_method' => fake()->randomElement(['geofence', 'manual', 'face_recognition']),
            'status' => 'pending_hr',
            'is_verified' => false,
            'notes' => null,
            'verified_by' => null,
            'verified_at' => null,
            'worked_hours' => round(($clockOutTime->getTimestamp() - $clockInTime->getTimestamp()) / 3600, 2),
            'is_late' => fake()->boolean(20),
            'is_early_leave' => fake()->boolean(15),
        ];
    }

    // ── Status States ──────────────────────────────────

    public function pendingHr(): static
    {
        return $this->state(fn () => [
            'status' => 'pending_hr',
            'is_verified' => false,
            'verified_by' => null,
            'verified_at' => null,
        ]);
    }

    public function approved(): static
    {
        return $this->state(fn () => [
            'status' => 'approved',
            'is_verified' => true,
            'verified_by' => User::factory(),
            'verified_at' => fake()->dateTimeBetween('-1 day', 'now'),
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn () => [
            'status' => 'rejected',
            'is_verified' => true,
            'verified_by' => User::factory(),
            'verified_at' => fake()->dateTimeBetween('-1 day', 'now'),
            'notes' => fake()->sentence(),
        ]);
    }

    // ── Clock In/Out States ────────────────────────────

    public function clockedIn(): static
    {
        return $this->state(fn () => [
            'clock_out' => null,
            'clock_out_at' => null,
            'clock_out_lat' => null,
            'clock_out_lng' => null,
            'clock_out_ip' => null,
            'clock_out_photo_path' => null,
            'clock_out_within_geofence' => null,
            'clock_out_method' => null,
            'worked_hours' => null,
        ]);
    }

    public function fullDay(): static
    {
        return $this->state(fn () => [
            'is_late' => false,
            'is_early_leave' => false,
            'clock_in_within_geofence' => true,
            'clock_out_within_geofence' => true,
        ]);
    }

    // ── Behavior States ────────────────────────────────

    public function late(): static
    {
        $lateTime = fake()->dateTimeBetween('08:30', '10:00');

        return $this->state(fn () => [
            'is_late' => true,
            'clock_in' => $lateTime,
            'clock_in_at' => $lateTime,
        ]);
    }

    public function earlyLeave(): static
    {
        $earlyTime = fake()->dateTimeBetween('13:00', '15:00');

        return $this->state(fn () => [
            'is_early_leave' => true,
            'clock_out' => $earlyTime,
            'clock_out_at' => $earlyTime,
        ]);
    }

    public function outsideGeofence(): static
    {
        return $this->state(fn () => [
            'clock_in_within_geofence' => false,
            'clock_out_within_geofence' => false,
            'clock_in_method' => 'manual',
            'clock_out_method' => 'manual',
        ]);
    }

    public function faceRecognized(): static
    {
        return $this->state(fn () => [
            'clock_in_method' => 'face_recognition',
            'clock_out_method' => 'face_recognition',
        ]);
    }

    // ── Date Scoping States ────────────────────────────

    public function today(): static
    {
        return $this->state(fn () => [
            'date' => now()->toDateString(),
            'clock_in' => now()->subHours(random_int(4, 8)),
            'clock_in_at' => now()->subHours(random_int(4, 8)),
            'clock_out' => null,
            'clock_out_at' => null,
            'worked_hours' => null,
            'status' => 'pending_hr',
            'is_verified' => false,
        ]);
    }

    public function forDate(string $date): static
    {
        $clockIn = Carbon::parse($date)->addHours(random_int(7, 9))->addMinutes(random_int(0, 30));
        $clockOut = Carbon::parse($date)->addHours(random_int(16, 18));

        return $this->state(fn () => [
            'date' => $date,
            'clock_in' => $clockIn,
            'clock_in_at' => $clockIn,
            'clock_out' => $clockOut,
            'clock_out_at' => $clockOut,
        ]);
    }

    // ── Relationship Override ──────────────────────────

    public function forEmployee(Employee $employee): static
    {
        return $this->state(fn () => [
            'employee_id' => $employee->id,
        ]);
    }

    public function forWorkplace(Workplace $workplace): static
    {
        return $this->state(fn () => [
            'workplace_id' => $workplace->id,
        ]);
    }
}
