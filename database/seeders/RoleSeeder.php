<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create the three roles
        Role::firstOrCreate(['name' => 'employee', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'hr', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'accounting', 'guard_name' => 'web']);
    }
}
