<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Grade;

class GradeSeeder extends Seeder
{
    public function run(): void
    {
        $grades = [
            '1. Sınıf',
            '2. Sınıf',
            '3. Sınıf',
            '4. Sınıf',
            '5. Sınıf',
            '6. Sınıf',
            '7. Sınıf',
            '8. Sınıf',
            '9. Sınıf',
            '10. Sınıf',
            '11. Sınıf',
            '12. Sınıf',
        ];

        foreach ($grades as $name) {
            Grade::firstOrCreate(
                ['name' => $name],
                ['description' => $name . ' sınıf seviyesi']
            );
        }
    }
}
