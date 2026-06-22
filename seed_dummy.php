<?php

$user = \App\Models\User::firstOrCreate(
    ['email' => 'employee@example.com'],
    [
        'name' => 'John Employee',
        'password' => bcrypt('password')
    ]
);

// If Spatie permissions are used:
if (class_exists(\Spatie\Permission\Models\Role::class)) {
    $role = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'employee', 'guard_name' => 'web']);
    $user->assignRole($role);
}

// Create an assignment for this user
$assignment = \App\Models\Assignment::create([
    'title' => 'Tugas Luar Kota Jakarta',
    'description' => 'Mengecek instalasi alat di Jakarta',
    'assigned_to' => $user->id,
    'created_by' => 1,
    'start_date' => now()->subDays(3),
    'end_date' => now()->subDay(),
    'is_active' => true,
    'notes' => 'Tugas selesai'
]);

echo "Created Employee: {$user->email} / password\n";
echo "Assignment ID: {$assignment->id}\n";
