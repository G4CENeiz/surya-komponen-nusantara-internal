<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            ['name' => 'Human Resources', 'code' => 'HRD', 'description' => 'Human Resources & Development department'],
            ['name' => 'Finance & Accounting', 'code' => 'FIN', 'description' => 'Finance and accounting operations'],
            ['name' => 'Information Technology', 'code' => 'IT', 'description' => 'IT infrastructure and development'],
            ['name' => 'Marketing', 'code' => 'MKT', 'description' => 'Marketing and brand management'],
            ['name' => 'Operations', 'code' => 'OPS', 'description' => 'Daily operations and logistics'],
            ['name' => 'Sales', 'code' => 'SLS', 'description' => 'Sales and business development'],
            ['name' => 'Legal', 'code' => 'LGL', 'description' => 'Legal and compliance'],
            ['name' => 'Administration', 'code' => 'ADM', 'description' => 'General administration'],
        ];

        foreach ($departments as $dept) {
            Department::updateOrCreate(
                ['code' => $dept['code']],
                $dept,
            );
        }
    }
}
