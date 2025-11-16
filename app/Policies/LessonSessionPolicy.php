<?php

namespace App\Policies;

use App\Models\User;
use App\Models\LessonSession;

class LessonSessionPolicy
{
    public function viewAny(User $user)
    {
        return $user->hasRole(['admin', 'manager', 'teacher']);
    }

    public function view(User $user, LessonSession $lesson)
    {
        return $user->hasRole('admin') ||
            ($user->hasRole('manager') && $lesson->classSchedule->class->school_id == $user->managerProfile->school_id) ||
            ($user->hasRole('teacher') && $lesson->teacher_id == $user->id);
    }

    public function create(User $user)
    {
        return $user->hasRole(['admin', 'teacher', 'manager']);
    }

    public function update(User $user, LessonSession $lesson)
    {
        return $user->hasRole('admin') ||
            ($user->hasRole('manager') && $lesson->classSchedule->class->school_id == $user->managerProfile->school_id);
    }

    public function delete(User $user, LessonSession $lesson)
    {
        return $user->hasRole('admin');
    }
}
