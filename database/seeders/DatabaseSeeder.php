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
        User::updateOrCreate(
            ['email' => 'hr@example.com'],
            ['name' => 'HR User', 'password' => bcrypt('password')]
        )->assignRole('hr');

        // Create Employee user
        User::updateOrCreate(
            ['email' => 'employee@example.com'],
            ['name' => 'Employee User', 'password' => bcrypt('password')]
        )->assignRole('employee');

        // Create Accounting user
        User::updateOrCreate(
            ['email' => 'accounting@example.com'],
            ['name' => 'Accounting User', 'password' => bcrypt('password')]
        )->assignRole('accounting');

        // Create 1,200 employees with realistic dummy data
        $this->command->info('Clearing existing data...');
        Employee::query()->delete();
        
        // Clear users except admin users using raw query
        \DB::table('model_has_roles')->delete();
        \DB::table('model_has_permissions')->delete();
        \DB::table('role_has_permissions')->delete();
        \DB::table('users')->where('email', 'not in', ['hr@example.com', 'employee@example.com', 'accounting@example.com'])->delete();

        $this->command->info('Creating 1,200 users for employees...');

        // Create users in bulk first
        $users = User::factory()->count(1200)->create();

        $this->command->info('Creating 1,200 employees...');

        // Create employees with the pre-created users
        $users->each(function ($user) {
            Employee::factory()->create([
                'user_id' => $user->id,
            ]);
        });

        $this->command->info('✓ Created '.Employee::count().' employees');
    }
}
