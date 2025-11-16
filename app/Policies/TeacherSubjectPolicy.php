<?php

namespace App\Policies;

use App\Models\User;
use App\Models\TeacherSubject;

class TeacherSubjectPolicy
{
    public function viewAny(User $user)
    {
        return $user->hasAnyRole(['admin', 'manager']);
    }

    public function create(User $user)
    {
        return $user->hasAnyRole(['admin', 'manager']);
    }

    public function delete(User $user, TeacherSubject $record)
    {
        return $user->hasAnyRole(['admin', 'manager']);
    }
}
