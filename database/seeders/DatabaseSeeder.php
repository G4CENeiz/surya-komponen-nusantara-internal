<?php

namespace Database\Seeders;

use App\Models\Employee;
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
            DepartmentSeeder::class,
            JobClassSeeder::class,
            WorkplaceSeeder::class,
        ]);

        // Create HR user
        User::factory()->create([
            'name' => 'HR User',
            'email' => 'hr@example.com',
        ])->assignRole('hr');

        // Create Employee user
        User::factory()->create([
            'name' => 'Employee User',
            'email' => 'employee@example.com',
        ])->assignRole('employee');

        // Create Accounting user
        User::factory()->create([
            'name' => 'Accounting User',
            'email' => 'accounting@example.com',
        ])->assignRole('accounting');

        // Create 1,200 employees with realistic dummy data
        $this->command->info('Creating 1,200 employees...');

        Employee::factory()
            ->count(1200)
            ->create();

        $this->command->info('✓ Created '.Employee::count().' employees');
    }
}
