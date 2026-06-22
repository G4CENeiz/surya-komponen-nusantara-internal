<?php

namespace Database\Seeders;

use App\Models\WorkLocation;
use Illuminate\Database\Seeder;

class WorkLocationSeeder extends Seeder
{
    public function run(): void
    {
        $locations = [
            [
                'name' => 'Head Office Jakarta',
                'code' => 'LOC-001',
                'address' => 'Jl. Sudirman Kav. 52-53, Jakarta Selatan, DKI Jakarta',
                'latitude' => -6.22689000,
                'longitude' => 106.81473000,
                'radius_meters' => 100,
                'is_active' => true,
            ],
            [
                'name' => 'Branch Office Bandung',
                'code' => 'LOC-002',
                'address' => 'Jl. Asia Afrika No. 15-17, Bandung, Jawa Barat',
                'latitude' => -6.91746000,
                'longitude' => 107.61912000,
                'radius_meters' => 100,
                'is_active' => true,
            ],
            [
                'name' => 'Branch Office Surabaya',
                'code' => 'LOC-003',
                'address' => 'Jl. Pemuda No. 27-31, Surabaya, Jawa Timur',
                'latitude' => -7.25747000,
                'longitude' => 112.75209000,
                'radius_meters' => 150,
                'is_active' => true,
            ],
            [
                'name' => 'Warehouse Semarang',
                'code' => 'LOC-004',
                'address' => 'Jl. Sultan Agung No. 88, Semarang, Jawa Tengah',
                'latitude' => -6.96661000,
                'longitude' => 110.41967000,
                'radius_meters' => 200,
                'is_active' => true,
            ],
        ];

        foreach ($locations as $loc) {
            WorkLocation::updateOrCreate(
                ['code' => $loc['code']],
                $loc,
            );
        }
    }
}
