<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Branch;

class BranchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $branches = [
            'Türkçe',
            'Matematik',
            'Fen Bilimleri',
            'Sosyal Bilgiler',
            'İngilizce',
            'Beden Eğitimi',
            "Müzik",
            "Görsel Sanatlar",
            "Din Kültürü ve Ahlak Bilgisi",
            "Rehberlik",
            "Bilgisayar Bilimleri",
            "Tarih",
            "Coğrafya",
            "Felsefe",
            "Kimya",
            "Fizik",
            "Biyoloji",
            "Edebiyat",
            "Dil ve Anlatım",
            "Geometri",
            "Almanca",
            "Fransızca",
            "İspanyolca",
        ];

        foreach ($branches as $branch) {
            Branch::firstOrCreate(['name' => $branch]);
        }
    }
}
