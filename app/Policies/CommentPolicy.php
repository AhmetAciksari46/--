<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Message;
use App\Enums\GroupType;

class CommentPolicy
{
    /**
     * Yorum yazma yetkisi
     */
    public function create(User $user, Message $message): bool
    {
        $group = $message->group;

        // ❌ GLOBAL & SCHOOL gruplarında yorum YOK
        if (in_array($group->type, [
            GroupType::GlobalGeneral,
            GroupType::GlobalManager,
            GroupType::GlobalYonetim,
            GroupType::SchoolGeneral,
            GroupType::SchoolManagement,
        ])) {
            return false;
        }

        // ✅ SADECE CLASSROOM
        if ($group->type === GroupType::Classroom) {

            // Admin isterse yorum yazabilsin mi?
            if ($user->hasRole('admin')) {
                return true;
            }

            // Öğretmen & öğrenci
            if ($user->hasAnyRole(['teacher', 'schoolstudent'])) {
                return $user->isMemberOf($group);
            }

            // Manager (yorum KAPALI istedin)
            return false;
        }

        return false;
    }
}
