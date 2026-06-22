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
        $hr = User::firstOrCreate(
            ['email' => 'hr@example.com'],
            ['name' => 'HR User', 'password' => bcrypt('password'), 'email_verified_at' => now()]
        );
        if (! $hr->hasRole('hr')) {
            $hr->assignRole('hr');
        }

        // Create Employee user
        $emp = User::firstOrCreate(
            ['email' => 'employee@example.com'],
            ['name' => 'Employee User', 'password' => bcrypt('password'), 'email_verified_at' => now()]
        );
        if (! $emp->hasRole('employee')) {
            $emp->assignRole('employee');
        }

        // Create Accounting user
        $acc = User::firstOrCreate(
            ['email' => 'accounting@example.com'],
            ['name' => 'Accounting User', 'password' => bcrypt('password'), 'email_verified_at' => now()]
        );
        if (! $acc->hasRole('accounting')) {
            $acc->assignRole('accounting');
        }

        // Create 1,200 employees with realistic dummy data
        $this->command->info('Creating employees...');

        if (Employee::count() === 0) {
            Employee::factory()
                ->count(1200)
                ->create();
        }

        $this->command->info('✓ Total employees: '.Employee::count());

        // Seed payroll settings (overtime rate, late penalty, etc.)
        \App\Models\PayrollSetting::updateOrCreate(
            ['key' => 'overtime_rate'],
            ['name' => 'Tarif Lembur', 'value' => 50000, 'type' => 'number', 'description' => 'Tarif per jam lembur']
        );
        \App\Models\PayrollSetting::updateOrCreate(
            ['key' => 'late_penalty'],
            ['name' => 'Denda Keterlambatan', 'value' => 25000, 'type' => 'number', 'description' => 'Denda per kali terlambat']
        );
        \App\Models\PayrollSetting::updateOrCreate(
            ['key' => 'bpjs_kes_percent'],
            ['name' => 'BPJS Kesehatan (%)', 'value' => 1, 'type' => 'number', 'description' => 'Persentase iuran BPJS Kesehatan']
        );
        \App\Models\PayrollSetting::updateOrCreate(
            ['key' => 'bpjs_tk_percent'],
            ['name' => 'BPJS Ketenagakerjaan (%)', 'value' => 2, 'type' => 'number', 'description' => 'Persentase iuran BPJS Ketenagakerjaan']
        );
        $this->command->info('✓ Payroll settings seeded');

        $this->call([
            PayrollTestDataSeeder::class,
            HistoricalPayrollTestDataSeeder::class,
            AnnouncementSeeder::class,
            AssignmentSeeder::class,
        ]);

        // Seed reimbursements
        $this->command->info('Seeding reimbursements...');
        if (\App\Models\Reimbursement::count() === 0) {
            $usersWithRole = User::whereHas('roles', fn ($q) => $q->whereIn('name', ['hr', 'accounting', 'employee']))->get();
            $assignments = \App\Models\Assignment::all();

            for ($i = 0; $i < 50; $i++) {
                $user = $usersWithRole->random();
                $status = fake()->randomElement(['pending', 'approved', 'approved', 'approved', 'rejected']);
                $approvedBy = $status === 'approved' ? $hr->id : ($status === 'rejected' ? $hr->id : null);

                \App\Models\Reimbursement::create([
                    'user_id' => $user->id,
                    'assignment_id' => $assignments->pluck('id')->random() ?? null,
                    'amount' => fake()->randomFloat(2, 50000, 2000000),
                    'notes' => fake()->sentence(),
                    'attachment_path' => 'reimbursements/'.fake()->uuid().'.jpg',
                    'status' => $status,
                    'approved_at' => $status !== 'pending' ? fake()->dateTimeBetween('-1 month', 'now') : null,
                    'approved_by' => $approvedBy,
                ]);
            }
        }
        $this->command->info('✓ Reimbursements seeded: '.\App\Models\Reimbursement::count());
    }
}
