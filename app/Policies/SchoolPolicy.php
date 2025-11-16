<?php

namespace App\Policies;

use App\Models\School;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class SchoolPolicy
{
    public function manageTeacherPermissions(User $user, School $school, User $teacher)
    {
        // Admin her şeyi yapar
        if ($user->hasRole('admin')) {
            return true;
        }

        // Manager kendi okulunda değilse erişemez
        if ($user->hasRole('manager')) {
            if (!$user->managerProfile || $user->managerProfile->school_id != $school->id) {
                return false;
            }

            // Manager başka manager veya admin'in izinlerini düzenleyemez
            if (!$teacher->hasRole('teacher')) {
                return false;
            }

            // Öğretmen de o okula ait olmalı
            if (!$teacher->teacherProfile || $teacher->teacherProfile->school_id != $school->id) {
                return false;
            }

            return true;
        }

        return false;
    }

    public function view(User $user, School $school)
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user, School $school)
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        // Manager ise kendi okulu olmalı
        if ($user->hasRole('manager') && $user->managerProfile && $user->managerProfile->school_id == $school->id) {
            return true;
        }

        // Teacher ise kendi okulu olmalı
        if ($user->hasRole('teacher') && $user->teacherProfile && $user->teacherProfile->school_id == $school->id) {
            return true;
        }

        // Öğrenci ise kendi okulu olmalı
        if ($user->hasRole('schoolstudent') && $user->schoolStudentProfile && $user->schoolStudentProfile->school_id == $school->id) {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, School $school)
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        // Manager ise kendi okulu olmalı
        if ($user->hasRole('manager') && $user->managerProfile && $user->managerProfile->school_id == $school->id) {
            return true;
        }

        // Teacher ise kendi okulu olmalı
        if ($user->hasRole('teacher') && $user->teacherProfile && $user->teacherProfile->school_id == $school->id) {
            return true;
        }

        // Öğrenci ise kendi okulu olmalı
        if ($user->hasRole('schoolstudent') && $user->schoolStudentProfile && $user->schoolStudentProfile->school_id == $school->id) {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, School $school)
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        // Manager ise kendi okulu olmalı
        if ($user->hasRole('manager') && $user->managerProfile && $user->managerProfile->school_id == $school->id) {
            return true;
        }

        // Teacher ise kendi okulu olmalı
        if ($user->hasRole('teacher') && $user->teacherProfile && $user->teacherProfile->school_id == $school->id) {
            return true;
        }

        // Öğrenci ise kendi okulu olmalı
        if ($user->hasRole('schoolstudent') && $user->schoolStudentProfile && $user->schoolStudentProfile->school_id == $school->id) {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, School $school)
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        // Manager ise kendi okulu olmalı
        if ($user->hasRole('manager') && $user->managerProfile && $user->managerProfile->school_id == $school->id) {
            return true;
        }

        // Teacher ise kendi okulu olmalı
        if ($user->hasRole('teacher') && $user->teacherProfile && $user->teacherProfile->school_id == $school->id) {
            return true;
        }

        // Öğrenci ise kendi okulu olmalı
        if ($user->hasRole('schoolstudent') && $user->schoolStudentProfile && $user->schoolStudentProfile->school_id == $school->id) {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, School $school)
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        // Manager ise kendi okulu olmalı
        if ($user->hasRole('manager') && $user->managerProfile && $user->managerProfile->school_id == $school->id) {
            return true;
        }

        // Teacher ise kendi okulu olmalı
        if ($user->hasRole('teacher') && $user->teacherProfile && $user->teacherProfile->school_id == $school->id) {
            return true;
        }

        // Öğrenci ise kendi okulu olmalı
        if ($user->hasRole('schoolstudent') && $user->schoolStudentProfile && $user->schoolStudentProfile->school_id == $school->id) {
            return true;
        }
        return false;
    }
}
