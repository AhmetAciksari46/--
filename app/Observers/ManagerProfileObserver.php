<?php

namespace App\Observers;

use App\Models\ManagerProfile;
use App\Models\Group;
use App\Models\GroupMember;
use App\Enums\GroupType;

class ManagerProfileObserver
{
    /**
     * Manager profili oluşturulunca
     * (Bu aşamada school_id genelde NULL)
     */
    public function created(ManagerProfile $profile)
    {
        // Bilinçli olarak BOŞ
        // Çünkü manager önce oluşturuluyor, sonra okula bağlanıyor
    }

    /**
     * Manager profili güncellenince
     */
    public function updated(ManagerProfile $profile)
    {
        if (! $profile->wasChanged('school_id')) {
            return;
        }

        $oldSchoolId = $profile->getOriginal('school_id');
        $newSchoolId = $profile->school_id;

        // Eski okuldan çıkar
        if ($oldSchoolId) {
            $this->detachFromSchoolGroups($profile->user_id, $oldSchoolId);
        }

        // Yeni okula ekle
        if ($newSchoolId) {
            $this->attachToSchoolGroups($profile->user_id, $newSchoolId);
        }
    }

    /* ======================================================
     |  🔧 YARDIMCI METODLAR
     ====================================================== */

    private function attachToSchoolGroups(int $userId, int $schoolId): void
    {
        $groups = Group::where('school_id', $schoolId)
            ->whereIn('type', [
                GroupType::SchoolGeneral,
                GroupType::SchoolManagement,
            ])
            ->get();

        foreach ($groups as $group) {
            GroupMember::firstOrCreate([
                'group_id' => $group->id,
                'user_id'  => $userId,
            ], [
                'role_in_group' => 'manager',
            ]);
        }
    }

    private function detachFromSchoolGroups(int $userId, int $schoolId): void
    {
        GroupMember::where('user_id', $userId)
            ->whereHas('group', function ($q) use ($schoolId) {
                $q->where('school_id', $schoolId)
                    ->whereIn('type', [
                        GroupType::SchoolGeneral,
                        GroupType::SchoolManagement,
                    ]);
            })
            ->delete();
    }
}
