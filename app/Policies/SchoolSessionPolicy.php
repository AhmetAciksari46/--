<?php

namespace App\Policies;

use App\Models\SchoolSession;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Auth\Access\HandlesAuthorization;

class SchoolSessionPolicy
{
    use HandlesAuthorization;

    public function before(User $user, string $ability): ?bool
    {
        // Admin (Süper Yönetici) her zaman izinlidir.
        if ($user->isAdmin()) {
            return true;
        }
        return null;
    }

    /**
     * Oturumu yönetme yetkisi için ortak kontrol.
     */
    protected function manageSession(User $user, SchoolSession $session): bool
    {
        // 1. İzin Kontrolü
        $hasPermission = $user->isManager() || $user->can('manage_school_sessions');
        if (!$hasPermission) {
            return false;
        }

        // 2. KRİTİK OKUL AİTLİK KONTROLÜ
        // Oturumun kullanıcının okuluna ait olup olmadığını kontrol et
        $isOwnedBySchool = $user->school?->id === $session->school_id;

        return $isOwnedBySchool;
    }

    public function viewAny(User $user): bool
    {
        // Manager'lar tüm oturumları, Teacher'lar kendi oturumlarını görebilir.
        return $user->isManager() || $user->isTeacher() || $user->can('view_school_sessions');
    }

    public function view(User $user, SchoolSession $session): bool
    {
        // Yönetim yetkisi VEYA ilgili oturumun öğretmeni olma
        return $this->manageSession($user, $session) ||
            ($user->isTeacher() && $user->id === $session->teacher_id);
    }

    public function create(User $user): bool
    {
        // Oluşturma genellikle Manager yetkisindedir.
        return $user->isManager() || $user->can('manage_school_sessions');
    }

    public function update(User $user, SchoolSession $session): bool
    {
        return $this->manageSession($user, $session);
    }

    public function delete(User $user, SchoolSession $session): bool
    {
        return $this->manageSession($user, $session);
    }

    // YOKLAMA Yetkileri

    public function viewAttendance(User $user, SchoolSession $session): bool
    {
        // Oturumu yönetme veya görüntüleme yetkisi olanlar yoklamayı görebilir.
        return $this->view($user, $session);
    }

    public function recordAttendance(User $user, SchoolSession $session): bool
    {
        // Oturumun öğretmeni VEYA oturumu yönetme yetkisi olanlar yoklama kaydedebilir.
        $isTeacher = $user->isTeacher() && $user->id === $session->teacher_id;

        return $isTeacher || $this->manageSession($user, $session) || $user->can('record_attendance');
    }
}
