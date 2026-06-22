<?php

namespace Database\Seeders;

use App\Models\Office;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
        ]);

        // Create default office (example: Jakarta office)
        $office = Office::create([
            'name' => 'Main Office - Jakarta',
            'latitude' => -6.2088,
            'longitude' => 106.8456,
            'radius_meters' => 100,
            'work_start' => '08:00:00',
            'work_end' => '17:00:00',
        ]);

        User::factory()->create([
            'name' => 'HR User',
            'email' => 'hr@example.com',
        ])->assignRole('hr');

        User::factory()->create([
            'name' => 'Employee User',
            'email' => 'employee@example.com',
        ])->assignRole('employee');

        User::factory()->create([
            'name' => 'Accounting User',
            'email' => 'accounting@example.com',
        ])->assignRole('accounting');
    }
}
