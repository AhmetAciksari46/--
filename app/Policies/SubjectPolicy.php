<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Subject;
use Illuminate\Auth\Access\HandlesAuthorization;

class SubjectPolicy
{
    use HandlesAuthorization;
    protected $policies = [
        \App\Models\Subject::class => \App\Policies\SubjectPolicy::class,
    ];
    public function before(User $user, $ability)
    {
        if ($user->role = "admin") {
            return true;
        }
        return null; // Önemli: diğer policy'lerin çalışabilmesi için null dönülmeli
    }

    // Dersleri görme yetkisi (Okul kapsamında)
    public function viewAny(User $user): bool
    {
        // Manager'lar veya 'view_subjects' iznine sahip olanlar.
        return $user->role === "admin" || $user->isManager() || $user->can('view_subjects');
    }
    /**
     * Tek bir subject kaydını görüntüleme izni.
     */
    public function view(User $user, Subject $subject): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Ders oluşturma yetkisi.
     */
    public function create(User $user): bool
    {
        // Manager'lar veya 'manage_subjects' iznine sahip olanlar.
        return $user->isManager() || $user->can('manage_subjects');
    }

    /**
     * Tek bir dersi görüntüleme, güncelleme veya silme yetkisi için ortak kontrol.
     */
    protected function manageSubject(User $user, Subject $subject): bool
    {
        // 1. Yetki Kontrolü
        $hasPermission = $user->isManager() || $user->can('manage_subjects');

        if (!$hasPermission) {
            return false;
        }

        // 2. KRİTİK OKUL AİTLİK KONTROLÜ
        // Subject, kullanıcının okuluna ait olmalı
        $isOwnedBySchool = $user->school?->id === $subject->school_id;

        return $isOwnedBySchool;
    }



    public function update(User $user, Subject $subject): bool
    {
        return $this->manageSubject($user, $subject);
    }

    public function delete(User $user, Subject $subject): bool
    {
        return $this->manageSubject($user, $subject);
    }
}
