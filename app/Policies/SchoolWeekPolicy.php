<?php

namespace App\Policies;

use App\Models\User;
use App\Models\School;
use App\Models\SchoolWeek;
use Illuminate\Auth\Access\HandlesAuthorization;

class SchoolWeekPolicy
{
    use HandlesAuthorization;
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isAdmin()) {
            return true;
        }
        return null;
    }

    // Ortak aitlik ve izin kontrolü
    protected function manageSchoolWeek(User $user, SchoolWeek $schoolWeek): bool
    {
        $hasPermission = $user->isManager() || $user->can('manage_school_weeks');
        if (!$hasPermission) return false;

        $isOwnedBySchool = $user->school?->id === $schoolWeek->school_id;
        return $isOwnedBySchool;
    }

    public function viewAny(User $user, School $school): bool
    {
        // viewAny metodunda School nesnesi ile Manager'ın o okula ait olup olmadığını kontrol ediyoruz.
        return ($user->isManager() && $user->school?->id === $school->id)
            || $user->can('manage_school_weeks');
    }

    public function view(User $user, SchoolWeek $schoolWeek): bool
    {
        return $this->manageSchoolWeek($user, $schoolWeek);
    }

    public function create(User $user): bool
    {
        // Okul aitliği Controller'da yapıldığı için burada sadece genel izin yeterli
        return $user->isManager() || $user->can('manage_school_weeks');
    }

    public function update(User $user, SchoolWeek $schoolWeek): bool
    {
        return $this->manageSchoolWeek($user, $schoolWeek);
    }

    public function delete(User $user, SchoolWeek $schoolWeek): bool
    {
        return $this->manageSchoolWeek($user, $schoolWeek);
    }
}
