<?php

namespace Database\Seeders;

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
