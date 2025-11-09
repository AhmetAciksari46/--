<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Subject;

class SubjectSeeder extends Seeder
{
    public function run(): void
    {
        $subjects = [
            [
                'name' => 'Türkçe',
                'code' => 'TR101',
                'description' => 'Dil bilgisi, okuma-anlama ve yazma becerilerini geliştiren temel ders.',
                'is_active' => true,
            ],
            [
                'name' => 'Matematik',
                'code' => 'MT101',
                'description' => 'Sayısal düşünme, temel işlemler ve problem çözme becerileri.',
                'is_active' => true,
            ],
            [
                'name' => 'Fen Bilimleri',
                'code' => 'FB101',
                'description' => 'Doğa, canlılar ve fiziksel olaylar üzerine temel bilimsel kavramlar.',
                'is_active' => true,
            ],
            [
                'name' => 'Sosyal Bilgiler',
                'code' => 'SB101',
                'description' => 'Toplum, tarih ve coğrafya farkındalığını artıran ders.',
                'is_active' => true,
            ],
            [
                'name' => 'İngilizce',
                'code' => 'EN101',
                'description' => 'Temel yabancı dil becerilerini (okuma, yazma, konuşma, dinleme) geliştirir.',
                'is_active' => true,
            ],
            [
                'name' => 'Beden Eğitimi',
                'code' => 'PE101',
                'description' => 'Fiziksel gelişim, spor disiplini ve takım çalışması odaklı ders.',
                'is_active' => true,
            ],
            [
                'name' => 'Görsel Sanatlar',
                'code' => 'AR101',
                'description' => 'Yaratıcılığı ve el-göz koordinasyonunu geliştiren sanatsal ders.',
                'is_active' => true,
            ],
            [
                'name' => 'Müzik',
                'code' => 'MU101',
                'description' => 'Ritim, melodi ve müzik kültürü üzerine temel beceriler.',
                'is_active' => true,
            ],
        ];

        foreach ($subjects as $subject) {
            Subject::updateOrCreate(
                ['name' => $subject['name']],
                $subject
            );
        }

        $this->command->info('✅ SubjectSeeder başarıyla çalıştı. Global ders kayıtları oluşturuldu.');
    }
}
