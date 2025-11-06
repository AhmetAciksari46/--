<?php

namespace App\Policies;

use App\Models\User;

class TeacherPolicy
{
    /**
     * Manager sadece kendi okulundaki öğretmenlerin izinlerini güncelleyebilir.
     * Admin kullanıcıları bu policy’den otomatik olarak muaf (bypass) olur
     * çünkü AuthServiceProvider içinde Gate::before() ile tanımladık.
     */
    public function updatePermissions(User $manager, User $teacher): bool
    {
        // Manager değilse doğrudan reddet
        if ($manager->role !== 'manager') {
            return false;
        }

        // Manager'ın öğretmen profili var mı ve bir okula mı bağlı?
        $managerSchoolId = optional($manager->teacherProfile)->school_id;

        // Öğretmenin profili var mı ve bir okula mı bağlı?
        $teacherSchoolId = optional($teacher->teacherProfile)->school_id;

        // İki school_id aynı ise izin ver
        return $managerSchoolId && $teacherSchoolId && $managerSchoolId === $teacherSchoolId;
    }

    /**
     * Manager yalnızca kendi okulundaki öğretmenleri görebilir.
     */
    public function view(User $manager, User $teacher): bool
    {
        if ($manager->role !== 'manager') {
            return false;
        }

        return optional($manager->teacherProfile)->school_id === optional($teacher->teacherProfile)->school_id;
    }

    /**
     * Manager yalnızca kendi okulundaki öğretmenleri güncelleyebilir.
     */
    public function update(User $manager, User $teacher): bool
    {
        if ($manager->role !== 'manager') {
            return false;
        }

        return optional($manager->teacherProfile)->school_id === optional($teacher->teacherProfile)->school_id;
    }

    /**
     * Manager yalnızca kendi okulundaki öğretmenleri silebilir.
     */
    public function delete(User $manager, User $teacher): bool
    {
        if ($manager->role !== 'manager') {
            return false;
        }

        return optional($manager->teacherProfile)->school_id === optional($teacher->teacherProfile)->school_id;
    }

    /**
     * Manager yalnızca kendi okuluna öğretmen ekleyebilir.
     */
    public function create(User $manager): bool
    {
        return $manager->role === 'manager' && !empty(optional($manager->teacherProfile)->school_id);
    }
}
