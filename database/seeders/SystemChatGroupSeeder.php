<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Group;
use App\Enums\GroupType;

class SystemChatGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $createdBy = 1; // Varsayılan admin ID

        // ================================
        // 1) GLOBAL GENERAL (TÜM KULLANICILAR)
        // ================================
        Group::firstOrCreate(
            ['type' => GroupType::GlobalGeneral],
            [
                'name' => 'Genel Duyuru Grubu',
                'school_id' => null,
                'class_model_id' => null,
                'created_by' => $createdBy,
            ]
        );

        // ================================
        // 2) GLOBAL YÖNETİM (ADMIN + TEACHER + MANAGER)
        // ================================
        Group::firstOrCreate(
            ['type' => GroupType::GlobalYonetim],
            [
                'name' => 'Yönetim Ekibi Grubu',
                'school_id' => null,
                'class_model_id' => null,
                'created_by' => $createdBy,
            ]
        );

        // ================================
        // 3) GLOBAL MANAGER (TÜM MANAGERLAR)
        // ================================
        Group::firstOrCreate(
            ['type' => GroupType::GlobalManager],
            [
                'name' => 'Tüm Managerlar Grubu',
                'school_id' => null,
                'class_model_id' => null,
                'created_by' => $createdBy,
            ]
        );
    }
}
