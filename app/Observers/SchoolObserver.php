<?php

namespace App\Observers;

use App\Models\School;
use App\Models\Group;
use App\Enums\GroupType;

class SchoolObserver
{
    public function created(School $school)
    {
        // SCHOOL GENERAL
        $generalGroup = Group::create([
            'type' => GroupType::SchoolGeneral,
            'school_id' => $school->id,
            'name' => "{$school->name} Genel Grup",
            'created_by' => $school->created_by ?? 1,
        ]);

        // SCHOOL MANAGEMENT
        $managementGroup = Group::create([
            'type' => GroupType::SchoolManagement,
            'school_id' => $school->id,
            'name' => "{$school->name} Yönetim Grubu",
            'created_by' => $school->created_by ?? 1,
        ]);

        // Okul manager’ını otomatik ekle
        if ($school->manager_id) {
            $generalGroup->members()->firstOrCreate([
                'user_id' => $school->manager_id,
            ], [
                'role_in_group' => 'manager',
            ]);

            $managementGroup->members()->firstOrCreate([
                'user_id' => $school->manager_id,
            ], [
                'role_in_group' => 'manager',
            ]);
        }
    }


    /**
     * Okul silinmeden ÖNCE çalışır
     */
    public function deleting(School $school)
    {
        /**
         * 1️⃣ Okula bağlı sınıfları sil
         * → ClassModelObserver otomatik tetiklenir
         */
        foreach ($school->class_models as $classModel) {
            $classModel->delete();
        }

        /**
         * 2️⃣ Okula bağlı okul gruplarını sil
         */
        Group::where('school_id', $school->id)
            ->whereIn('type', [
                GroupType::SchoolGeneral,
                GroupType::SchoolManagement,
            ])
            ->each(function ($group) {
                $group->members()->delete();
                $group->delete();
            });
    }
}
