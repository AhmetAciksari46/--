<?php

namespace App\Policies;

use App\Models\StudentCurriculumOverride;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class StudentCurriculumOverridePolicy
{
    // Yüksek yetkili roller için hızlı geçiş (Süper Admin)
    public function before(User $user, string $ability): bool|null
    {
        if ($user->hasRole('admin')) {
            return true;
        }
        return null;
    }

    /**
     * Override listesini görüntüleme yetkisi
     */
    public function viewAny(User $user): Response
    {
        return $user->hasRole('manager')
            ? Response::allow()
            : Response::deny('Müfredat geçersiz kılma listesini görüntüleme yetkiniz yok.');
    }

    /**
     * Tek bir override kaydını görüntüleme yetkisi
     */
    public function view(User $user, StudentCurriculumOverride $studentCurriculumOverride): Response
    {
        // Manager ve Admin görebilir. Öğretmenler, kendi okullarındaki kaydı görebilir.
        return $user->hasRole('manager') || $user->isSchoolTeacher()
            ? Response::allow()
            : Response::deny('Bu müfredat geçersiz kılma kaydını görüntüleme yetkiniz yok.');
    }

    /**
     * Yeni bir override kaydı oluşturma yetkisi
     */
    public function create(User $user): Response
    {
        return $user->hasRole('manager')
            ? Response::allow()
            : Response::deny('Müfredat geçersiz kılma oluşturma yetkiniz yok.');
    }

    /**
     * Mevcut bir override kaydını güncelleme yetkisi
     */
    public function update(User $user, StudentCurriculumOverride $studentCurriculumOverride): Response
    {
        return $user->hasRole('manager')
            ? Response::allow()
            : Response::deny('Müfredat geçersiz kılma kaydını güncelleme yetkiniz yok.');
    }

    /**
     * Bir override kaydını silme yetkisi
     */
    public function delete(User $user, StudentCurriculumOverride $studentCurriculumOverride): Response
    {
        // Silme işlemi daha hassastır, sadece Manager yapabilir.
        return $user->hasRole('manager')
            ? Response::allow()
            : Response::deny('Müfredat geçersiz kılma kaydını silme yetkiniz yok.');
    }
}
