<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Subject;

class SubjectSeeder extends Seeder
{
    public function run(): void
    {
        $subjects = [
            'Matematik',
            'Türkçe',
            'Fen Bilimleri',
            'Sosyal Bilgiler',
            'İngilizce',
            'Beden Eğitimi',
        ];

        foreach ($subjects as $name) {
            Subject::updateOrCreate(['name' => $name], []);
        }
    }
}
