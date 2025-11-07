<?php

namespace App\Policies;

use App\Models\User;
use App\Models\School;
use App\Models\SchoolWeekDay;
use Illuminate\Auth\Access\HandlesAuthorization;

class SchoolDayPolicy
{
    use HandlesAuthorization;

    public function before(User $user, string $ability): ?bool
    {
        if ($user->isAdmin()) {
            return true;
        }
        return null;
    }

    /**
     * Tüm SchoolDay kayıtlarını listeleme yetkisi.
     */
    public function viewAny(User $user, School $school): bool
    {
        // Manager'lar veya 'manage_school_days' iznine sahip olanlar VE okula bağlı olmalı
        return ($user->isManager() && $user->school?->id === $school->id)
            || $user->can('manage_school_days');
    }

    /**
     * Belirli bir SchoolDay kaydını güncelleme yetkisi.
     */
    public function update(User $user, SchoolWeekDay $schoolDay): bool
    {
        // Manager veya 'manage_school_days' iznine sahip olmalı
        $hasPermission = $user->isManager() || $user->can('manage_school_days');

        if (!$hasPermission) {
            return false;
        }

        // KRİTİK OKUL AİTLİK KONTROLÜ
        $isOwnedBySchool = $user->school?->id === $schoolDay->school_id;

        return $isOwnedBySchool;
    }
}
