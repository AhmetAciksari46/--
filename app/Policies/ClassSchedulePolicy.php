<?php

namespace App\Policies;

use App\Models\User;
use App\Models\ClassSchedule;

class ClassSchedulePolicy
{
    public function viewAny(User $user)
    {
        return $user->hasRole(['admin', 'manager', 'teacher']);
    }

    public function view(User $user, ClassSchedule $schedule)
    {
        return $user->hasRole('admin')
            || ($user->hasRole('manager') && $schedule->class->school_id === $user->managerProfile->school_id)
            || ($user->hasRole('teacher') && $schedule->teacher_id === $user->id);
    }

    public function create(User $user)
    {
        return $user->hasRole(['admin', 'manager']);
    }

    public function update(User $user, ClassSchedule $schedule)
    {
        return $user->hasRole('admin')
            || ($user->hasRole('manager') && $schedule->class->school_id === $user->managerProfile->school_id);
    }

    public function delete(User $user, ClassSchedule $schedule)
    {
        return $user->hasRole('admin');
    }
}
