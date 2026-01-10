<?php

namespace App\Observers;

use App\Models\User;
use App\Models\Group;
use App\Models\GroupMember;
use App\Enums\GroupType;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

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

        /**
         * ✅ DEFAULT PERMISSIONS
         * → is_default = true olan permissionları user'a gerçekten ata
         * → böylece list ve revoke düzgün çalışır
         */
        $this->attachDefaultPermissions($user);
        $this->attachRoleBasedDefaultPermissions($user); // config by_role

    }

    /**
     * Kullanıcının rolü değişirse
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

        /**
         * ⚠️ Burada default permissionları tekrar basmıyoruz!
         * Çünkü admin kullanıcı default permissionı revoke etmiş olabilir.
         * Eğer burada yeniden verirsek revoke boşa düşer.
         *
         * Eğer role-based default gibi özel bir yapı istersen ayrıca tasarlarız.
         */
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

        // Sonra created mantığını tekrar uygula (gruplar için)
        $this->createdGroupsOnly($user);
    }

    /**
     * created() içindeki group mantığını ayrı çalıştırmak için.
     * created() içinde default permission var, ama rol update'te tekrar vermek istemiyoruz.
     */
    private function createdGroupsOnly(User $user): void
    {
        $this->attachToGroup($user, GroupType::GlobalGeneral);

        if ($user->hasRole('manager')) {
            $this->attachToGroup($user, GroupType::GlobalManager);
        }

        if ($user->hasAnyRole(['admin', 'manager', 'teacher'])) {
            $this->attachToGroup($user, GroupType::GlobalYonetim);
        }
    }

    /**
     * Default permissionları user'a gerçekten attach eder.
     */
    private function attachDefaultPermissions(User $user): void
    {
        $defaultPermissions = Permission::query()
            ->where('is_default', true)
            ->pluck('name');

        if ($defaultPermissions->isEmpty()) {
            return;
        }

        $user->givePermissionTo($defaultPermissions);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function attachRoleBasedDefaultPermissions(User $user): void
    {
        $role = $user->role;

        // config'ten role default permissions listesini al
        $roleDefaults = config("default_permissions.by_role.{$role}", []);

        if (empty($roleDefaults)) {
            return;
        }

        $permissions = Permission::query()
            ->whereIn('name', $roleDefaults)
            ->where('guard_name', 'sanctum')
            ->pluck('name');

        if ($permissions->isNotEmpty()) {
            $user->givePermissionTo($permissions);
        }

        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
