<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Subject;

class SubjectPolicy
{
    public function viewAny(User $user)
    {
        return $user->hasRole('admin');
    }

    public function create(User $user)
    {
        return $user->hasRole('admin');
    }

    public function update(User $user, Subject $subject)
    {
        return $user->hasRole('admin');
    }

    public function delete(User $user, Subject $subject)
    {
        return $user->hasRole('admin');
    }
}
