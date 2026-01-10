<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Group;
use App\Enums\GroupType;
use App\Models\GroupMember;
use App\Models\User;

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
        $admin = User::find($createdBy);

        if ($admin) {
            $systemGroups = Group::whereIn('type', [
                GroupType::GlobalGeneral,
                GroupType::GlobalYonetim,
                GroupType::GlobalManager
            ])->get(['id']);

            foreach ($systemGroups as $group) {
                GroupMember::firstOrCreate([
                    'group_id' => $group->id,
                    'user_id'  => $admin->id,
                ], [
                    'role_in_group' => 'admin'
                ]);
            }
        }
    }
}
