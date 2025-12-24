<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Group;
use App\Enums\GroupType;

class MessagePolicy
{
    /**
     * Mesaj yazma yetkisi
     */
    public function send(User $user, Group $group): bool
    {
        // 🔑 Admin her yere yazabilir
        if ($user->hasRole('admin')) {
            return true;
        }

        return match ($group->type) {

            /** 🌍 GLOBAL GRUPLAR */
            GroupType::GlobalGeneral,
            GroupType::GlobalManager,
            GroupType::GlobalYonetim
            => false, // admin harici kimse yazamaz

            /** 🏫 SCHOOL GRUPLARI */
            GroupType::SchoolGeneral,
            GroupType::SchoolManagement
            => $user->hasRole('manager')
                && $user->getSchoolId() === $group->school_id,

            /** 🧑‍🏫 CLASSROOM */
            GroupType::Classroom
            => (
                $user->hasAnyRole(['teacher', 'schoolstudent'])
                && $user->isMemberOf($group)
            )
                || (
                    $user->hasRole('manager')
                    && $user->getSchoolId() === $group->school_id
                ),

            default => false,
        };
    }
}
