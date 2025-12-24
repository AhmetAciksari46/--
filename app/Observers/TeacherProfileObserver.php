<?php

namespace App\Observers;

use App\Models\TeacherProfile;
use App\Models\Group;
use App\Models\GroupMember;
use App\Enums\GroupType;
use App\Models\ClassModel;

class TeacherProfileObserver
{
    /**
     * Öğretmen profili oluşturulunca
     */
    public function created(TeacherProfile $profile)
    {
        $this->attachToSchoolGroups($profile->user_id, $profile->school_id);

        // Eğer öğretmen bir sınıfa atanmışsa
        if (!empty($profile->class_model_id)) {
            $this->attachToClassGroup($profile->user_id, $profile->class_model_id);
        }
    }

    /**
     * Öğretmen profili güncellenince
     */
    public function updated(TeacherProfile $profile)
    {
        // Okul değiştiyse
        if ($profile->wasChanged('school_id')) {
            $oldSchoolId = $profile->getOriginal('school_id');
            $newSchoolId = $profile->school_id;

            if ($oldSchoolId) {
                $this->detachFromSchoolGroups($profile->user_id, $oldSchoolId);
            }

            if ($newSchoolId) {
                $this->attachToSchoolGroups($profile->user_id, $newSchoolId);
            }
        }

        // Sınıf değiştiyse
        if ($profile->wasChanged('class_model_id')) {
            $oldClassId = $profile->getOriginal('class_model_id');
            $newClassId = $profile->class_model_id;

            if ($oldClassId) {
                $this->detachFromClassGroup($profile->user_id, $oldClassId);
            }

            if ($newClassId) {
                $this->attachToClassGroup($profile->user_id, $newClassId);
            }
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
                'role_in_group' => 'teacher',
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

    private function attachToClassGroup(int $userId, int $classId): void
    {
        $class = ClassModel::find($classId);

        if (! $class || ! $class->chatGroup) {
            return;
        }

        GroupMember::firstOrCreate([
            'group_id' => $class->chatGroup->id,
            'user_id'  => $userId,
        ], [
            'role_in_group' => 'teacher',
        ]);
    }

    private function detachFromClassGroup(int $userId, int $classId): void
    {
        $class = ClassModel::find($classId);

        if (! $class || ! $class->chatGroup) {
            return;
        }

        GroupMember::where('group_id', $class->chatGroup->id)
            ->where('user_id', $userId)
            ->delete();
    }
}
