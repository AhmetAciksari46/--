<?php

namespace App\Policies;

use App\Models\User;
use App\Models\SchoolHasGrade;

class SchoolHasGradePolicy
{
    public function viewAny(User $user)
    {
        return $user->hasRole(['admin', 'manager']);
    }

    public function create(User $user, $school_id)
    {
        return $user->hasRole('admin') ||
            ($user->hasRole('manager') && $user->managerProfile->school_id == $school_id);
    }

    public function delete(User $user, SchoolHasGrade $record)
    {
        return $user->hasRole('admin') ||
            ($user->hasRole('manager') && $record->school_id == $user->managerProfile->school_id);
    }
}
