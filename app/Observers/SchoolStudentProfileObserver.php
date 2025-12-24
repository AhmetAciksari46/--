<?php

namespace App\Observers;

use App\Models\SchoolStudentProfile;
use App\Models\ClassModel;
use App\Models\GroupMember;

class SchoolStudentProfileObserver
{
    /**
     * Öğrenci profili oluşturulunca
     */
    public function created(SchoolStudentProfile $profile)
    {
        $this->attachToClassGroup($profile->user_id, $profile->active_class_id);
    }

    /**
     * Öğrenci profili güncellenince
     */
    public function updated(SchoolStudentProfile $profile)
    {
        // active_class_id değişmemişse çık
        if (! $profile->wasChanged('active_class_id')) {
            return;
        }

        $oldClassId = $profile->getOriginal('active_class_id');
        $newClassId = $profile->active_class_id;

        // Eski sınıf grubundan çıkar
        if ($oldClassId) {
            $this->detachFromClassGroup($profile->user_id, $oldClassId);
        }

        // Yeni sınıf grubuna ekle
        if ($newClassId) {
            $this->attachToClassGroup($profile->user_id, $newClassId);
        }
    }

    /* ======================================================
     |  🔧 YARDIMCI METODLAR
     ====================================================== */

    private function attachToClassGroup(int $userId, ?int $classId): void
    {
        if (! $classId) {
            return;
        }

        $class = ClassModel::find($classId);

        if (! $class || ! $class->chatGroup) {
            return;
        }

        GroupMember::firstOrCreate([
            'group_id' => $class->chatGroup->id,
            'user_id'  => $userId,
        ], [
            'role_in_group' => 'student',
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
