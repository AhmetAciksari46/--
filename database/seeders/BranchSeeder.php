<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Branch;
use Illuminate\Support\Str;

class BranchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $branches = [
            [
                'name' => 'Matematik',
                'slug' => Str::slug('Matematik', '-', 'tr'),
                'code' => 'MATH',
                'color' => '#1E90FF',
                'is_active' => true,
            ],
            [
                'name' => 'Türkçe',
                'slug' => Str::slug('Türkçe', '-', 'tr'),
                'code' => 'TR',
                'color' => '#FF6347',
                'is_active' => true,
            ],
            [
                'name' => 'Fen Bilimleri',
                'slug' => Str::slug('Fen Bilimleri', '-', 'tr'),
                'code' => 'SCI',
                'color' => '#32CD32',
                'is_active' => true,
            ],
        ];

        foreach ($branches as $branch) {
            Branch::updateOrCreate(['slug' => $branch['slug']], $branch);
        }
    }
}
