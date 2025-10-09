<?php

namespace App\Helpers;

use App\Models\TeacherProfile;
use App\Models\ManagerProfile;
use App\Models\SchoolStudentProfile;

class UserProfileHelper
{
    /**
     * Kullanıcının profilinin mevcut olup olmadığını kontrol et.
     * Eğer yoksa false döner.
     */
    public static function profileExists($user)
    {
        if (!$user) return false;

        switch ($user->role) {
            case 'teacher':
                return TeacherProfile::where('user_id', $user->id)->exists();

            case 'manager':
                return ManagerProfile::where('user_id', $user->id)->exists();

            case 'schoolstudent':
                return SchoolStudentProfile::where('user_id', $user->id)->exists();

            default:
                return false;
        }
    }
}
