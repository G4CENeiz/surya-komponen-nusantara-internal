<?php

namespace Database\Seeders;

use App\Models\JobClass;
use Illuminate\Database\Seeder;

class JobClassSeeder extends Seeder
{
    public function run(): void
    {
        $jobClasses = [
            [
                'name' => 'Staff',
                'code' => 'STF-001',
                'level' => 1,
                'min_salary' => 3_500_000,
                'max_salary' => 5_500_000,
                'base_allowance' => 500_000,
                'other_allowances' => 200_000,
                'description' => 'Entry-level staff position',
            ],
            [
                'name' => 'Senior Staff',
                'code' => 'STF-002',
                'level' => 2,
                'min_salary' => 5_500_000,
                'max_salary' => 8_000_000,
                'base_allowance' => 800_000,
                'other_allowances' => 300_000,
                'description' => 'Experienced staff with additional responsibilities',
            ],
            [
                'name' => 'Supervisor',
                'code' => 'SUP-001',
                'level' => 3,
                'min_salary' => 8_000_000,
                'max_salary' => 12_000_000,
                'base_allowance' => 1_200_000,
                'other_allowances' => 500_000,
                'description' => 'Team supervisor with management duties',
            ],
            [
                'name' => 'Manager',
                'code' => 'MGR-001',
                'level' => 4,
                'min_salary' => 12_000_000,
                'max_salary' => 18_000_000,
                'base_allowance' => 2_000_000,
                'other_allowances' => 800_000,
                'description' => 'Department manager',
            ],
            [
                'name' => 'Senior Manager',
                'code' => 'MGR-002',
                'level' => 5,
                'min_salary' => 18_000_000,
                'max_salary' => 25_000_000,
                'base_allowance' => 3_000_000,
                'other_allowances' => 1_000_000,
                'description' => 'Senior manager overseeing multiple teams',
            ],
            [
                'name' => 'Director',
                'code' => 'DIR-001',
                'level' => 6,
                'min_salary' => 25_000_000,
                'max_salary' => 40_000_000,
                'base_allowance' => 5_000_000,
                'other_allowances' => 2_000_000,
                'description' => 'Director-level executive',
            ],
        ];

        foreach ($jobClasses as $jc) {
            JobClass::updateOrCreate(
                ['code' => $jc['code']],
                $jc,
            );
        }
    }
}
