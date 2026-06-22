<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            ['name' => 'Produksi', 'code' => 'PRD', 'description' => 'Production and manufacturing department'],
            ['name' => 'HRD', 'code' => 'HRD', 'description' => 'Human Resources & Development department'],
            ['name' => 'Keuangan', 'code' => 'FIN', 'description' => 'Finance and accounting operations'],
            ['name' => 'IT', 'code' => 'IT', 'description' => 'Information Technology department'],
            ['name' => 'Gudang', 'code' => 'GDG', 'description' => 'Warehouse and inventory management'],
            ['name' => 'Operasional', 'code' => 'OPS', 'description' => 'Daily operations and logistics'],
        ];

        foreach ($departments as $dept) {
            Department::updateOrCreate(
                ['code' => $dept['code']],
                $dept,
            );
        }
    }
}
