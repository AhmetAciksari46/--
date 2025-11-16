<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Attendance;
use App\Models\LessonSession;

class AttendancePolicy
{
    public function viewAny(User $user)
    {
        return $user->hasRole(['admin', 'manager', 'teacher']);
    }

    public function view(User $user, LessonSession $lesson)
    {
        return $user->hasRole('admin')
            || ($user->hasRole('manager') && $lesson->schedule->class->school_id === $user->managerProfile->school_id)
            || ($user->hasRole('teacher') && $lesson->teacher_id === $user->id);
    }

    public function create(User $user, LessonSession $lesson)
    {
        return $user->hasRole('admin')
            || ($user->hasRole('teacher') && $lesson->teacher_id === $user->id);
    }

    public function update(User $user, Attendance $attendance)
    {
        return $user->hasRole('admin')
            || ($user->hasRole('teacher') && $attendance->session->teacher_id === $user->id);
    }

    public function delete(User $user, Attendance $attendance)
    {
        return $user->hasRole('admin');
    }
}
