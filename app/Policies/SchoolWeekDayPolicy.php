<?php

namespace App\Policies;

use App\Models\User;
use App\Models\SchoolWeekDay;
use App\Models\School;
use Illuminate\Auth\Access\HandlesAuthorization;

class SchoolWeekDayPolicy
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
    protected function manageSchoolDay(User $user, SchoolWeekDay $schoolDay): bool
    {
        $hasPermission = $user->isManager() || $user->can('manage_school_days');
        if (!$hasPermission) return false;

        $isOwnedBySchool = $user->school?->id === $schoolDay->school_id;
        return $isOwnedBySchool;
    }

    public function viewAny(User $user, School $school): bool
    {
        return ($user->isManager() && $user->school?->id === $school->id)
            || $user->can('manage_school_days');
    }

    public function view(User $user, SchoolWeekDay $schoolDay): bool
    {
        return $this->manageSchoolDay($user, $schoolDay);
    }

    public function create(User $user): bool
    {
        return $user->isManager() || $user->can('manage_school_days');
    }

    public function update(User $user, SchoolWeekDay $schoolDay): bool
    {
        return $this->manageSchoolDay($user, $schoolDay);
    }

    public function delete(User $user, SchoolWeekDay $schoolDay): bool
    {
        return $this->manageSchoolDay($user, $schoolDay);
    }
}
