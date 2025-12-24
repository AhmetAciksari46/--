<?php

namespace App\Observers;

use App\Models\User;
use App\Models\Group;
use App\Models\GroupMember;
use App\Enums\GroupType;

class UserObserver
{
    /**
     * Kullanıcı oluşturulduğunda çalışır
     */
    public function created(User $user)
    {
        /**
         * 1️⃣ GLOBAL GENEL
         * → tüm kullanıcılar
         */
        $this->attachToGroup($user, GroupType::GlobalGeneral);

        /**
         * 2️⃣ GLOBAL MANAGER
         * → sadece manager
         */
        if ($user->hasRole('manager')) {
            $this->attachToGroup($user, GroupType::GlobalManager);
        }

        /**
         * 3️⃣ GLOBAL YÖNETİM
         * → admin + manager + teacher
         */
        if ($user->hasAnyRole(['admin', 'manager', 'teacher'])) {
            $this->attachToGroup($user, GroupType::GlobalYonetim);
        }
    }

    /**
     * Kullanıcının rolü değişirse (opsiyonel ama önerilir)
     */
    public function updated(User $user)
    {
        if (! $user->wasChanged('role')) {
            return;
        }

        /**
         * Rol değiştiyse:
         * - global grupları yeniden senkronla
         */
        $this->syncGlobalGroups($user);
    }

    /* ======================================================
     |  🔧 YARDIMCI METODLAR
     ====================================================== */

    private function attachToGroup(User $user, GroupType $type): void
    {
        $group = Group::where('type', $type)->first();

        if (! $group) {
            return;
        }

        GroupMember::firstOrCreate([
            'group_id' => $group->id,
            'user_id'  => $user->id,
        ], [
            'role_in_group' => $user->role,
        ]);
    }

    private function syncGlobalGroups(User $user): void
    {
        // Önce tüm global gruplardan çıkar
        GroupMember::where('user_id', $user->id)
            ->whereHas('group', function ($q) {
                $q->whereIn('type', [
                    GroupType::GlobalGeneral,
                    GroupType::GlobalManager,
                    GroupType::GlobalYonetim,
                ]);
            })
            ->delete();

        // Sonra created mantığını tekrar uygula
        $this->created($user);
    }
}
