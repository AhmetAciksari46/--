<?php

namespace Database\Seeders;

use App\Models\Package;
use Illuminate\Database\Seeder;

class PackageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $packages = [
            [
                'name' => 'Standart Okul Paketi',
                'description' => 'Okullar için temel modülleri içeren yıllık plan.',
                'duration_days' => 365,
                'price' => 1999.00,
                'type' => 'school',
                'is_active' => true,
                'has_homework_module' => true,
                'has_schedule_module' => true,
                'week_count' => 36,
                'has_exam_module' => true,
                'has_chat_module' => false,
                'has_analytics_module' => true,
                'has_certificate_module' => false,
                'is_visible' => true,
                'is_trial' => false,
                'trial_days' => 0,
                'sort_order' => 1,
                'img_path' => null,
                'is_sequential_content_required' => false,
            ],
            [
                'name' => 'Öğrenci Premium Paketi',
                'description' => 'Bireysel öğrenciler için sınav ve ödev destekli premium paket.',
                'duration_days' => 180,
                'price' => 749.90,
                'type' => 'student',
                'is_active' => true,
                'has_homework_module' => true,
                'has_schedule_module' => true,
                'week_count' => 24,
                'has_exam_module' => true,
                'has_chat_module' => true,
                'has_analytics_module' => true,
                'has_certificate_module' => true,
                'is_visible' => true,
                'is_trial' => true,
                'trial_days' => 14,
                'sort_order' => 2,
                'img_path' => null,
                'is_sequential_content_required' => true,
            ],
            [
                'name' => 'Hızlandırılmış Yaz Kampı',
                'description' => 'Yaz dönemi için yoğunlaştırılmış içeriklere sahip kısa süreli paket.',
                'duration_days' => 60,
                'price' => 399.50,
                'type' => 'other',
                'is_active' => true,
                'has_homework_module' => true,
                'has_schedule_module' => true,
                'week_count' => 8,
                'has_exam_module' => false,
                'has_chat_module' => true,
                'has_analytics_module' => false,
                'has_certificate_module' => false,
                'is_visible' => true,
                'is_trial' => false,
                'trial_days' => 0,
                'sort_order' => 3,
                'img_path' => null,
                'is_sequential_content_required' => false,
            ],
        ];

        foreach ($packages as $package) {
            Package::updateOrCreate(['name' => $package['name']], $package);
        }
    }
}
