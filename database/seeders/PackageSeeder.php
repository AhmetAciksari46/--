<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB; // BU SATIRI EKLEYİN

class PackageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('packages')->insert([
    [
        'name' => 'Paket 1',
        'max_students' => 100,
        'max_teachers' => 5,
        'duration_days' => 365,
        'price' => 1000.00,
    ],
    [
        'name' => 'Paket 2',
        'max_students' => 200,
        'max_teachers' => 10,
        'duration_days' => 365,
        'price' => 2000.00,
    ],
]);

    }
}
