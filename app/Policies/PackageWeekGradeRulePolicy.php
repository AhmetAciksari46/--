<?php

namespace App\Policies;

use App\Models\User;
use App\Models\PackageWeekGradeRule;
use App\Models\School;
use Illuminate\Auth\Access\HandlesAuthorization;

class PackageWeekGradeRulePolicy
{
    use HandlesAuthorization;

    public function before(User $user, string $ability): ?bool
    {
        if ($user->isAdmin()) {
            return true;
        }
        return null;
    }

    // Okul paketi kontrolü için viewAny metoduna School $school nesnesini ekliyoruz.
    public function viewAny(User $user, School $school): bool
    {
        // Manager'ın okul ID'si URL'deki okul ID'si ile eşleşmeli VE izin olmalı.
        $isManagerForSchool = $user->isManager() && $user->school?->id === $school->id;
        $hasPermission = $user->can('manage_package_rules');

        return $isManagerForSchool || $hasPermission;
    }

    /**
     * Kuralı yönetme yetkisi.
     */
    public function manageRule(User $user, PackageWeekGradeRule $rule): bool
    {
        // Yetki kontrolü
        $hasPermission = $user->isManager() || $user->can('manage_package_rules');
        if (!$hasPermission) {
            return false;
        }

        // Okul Aitlik Kontrolü: Kuralın paketi, kullanıcının okulunun paketi olmalı.
        $isOwnedBySchoolPackage = $user->school?->package_id === $rule->package_id;

        return $isOwnedBySchoolPackage;
    }

    public function view(User $user, PackageWeekGradeRule $rule): bool
    {
        return $this->manageRule($user, $rule);
    }

    public function create(User $user): bool
    {
        // Store metodunda School ID kontrolü yapıldığı için burada genel izin yeterlidir.
        return $user->isManager() || $user->can('manage_package_rules');
    }

    public function update(User $user, PackageWeekGradeRule $rule): bool
    {
        return $this->manageRule($user, $rule);
    }

    public function delete(User $user, PackageWeekGradeRule $rule): bool
    {
        return $this->manageRule($user, $rule);
    }
}
