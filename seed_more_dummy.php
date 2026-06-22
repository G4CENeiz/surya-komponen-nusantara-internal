<?php

$user = \App\Models\User::where('email', 'employee@example.com')->first();

if (!$user) {
    echo "Employee not found!\n";
    exit;
}

$assignments = [
    [
        'title' => 'Tugas Maintenance Server Surabaya',
        'description' => 'Melakukan pemeliharaan rutin pada server di cabang Surabaya.',
        'notes' => 'Surabaya',
        'start_date' => now()->subDays(10),
        'end_date' => now()->subDays(8),
    ],
    [
        'title' => 'Tugas Survey Lokasi Bandung',
        'description' => 'Survey kelayakan lokasi pabrik baru di Bandung.',
        'notes' => 'Bandung',
        'start_date' => now()->subDays(7),
        'end_date' => now()->subDays(5),
    ],
    [
        'title' => 'Tugas Pelatihan Staf Yogyakarta',
        'description' => 'Memberikan pelatihan software internal ke staf Yogyakarta.',
        'notes' => 'Yogyakarta',
        'start_date' => now()->subDays(5),
        'end_date' => now()->subDays(3),
    ],
    [
        'title' => 'Tugas Audit Inventaris Semarang',
        'description' => 'Melakukan pengecekan dan audit barang di gudang Semarang.',
        'notes' => 'Semarang',
        'start_date' => now()->subDays(2),
        'end_date' => now()->subDays(1),
    ],
    [
        'title' => 'Tugas Meeting Vendor Bali',
        'description' => 'Mewakili perusahaan untuk bertemu dengan vendor utama di Bali.',
        'notes' => 'Bali',
        'start_date' => now()->subDays(15),
        'end_date' => now()->subDays(12),
    ],
];

foreach ($assignments as $data) {
    $a = \App\Models\Assignment::create([
        'title' => $data['title'],
        'description' => $data['description'],
        'assigned_to' => $user->id,
        'created_by' => 1,
        'start_date' => $data['start_date'],
        'end_date' => $data['end_date'],
        'is_active' => true,
        'notes' => $data['notes'],
    ]);
    echo "Created assignment: {$a->title}\n";
}

echo "Done generating 5 dummy assignments.\n";
