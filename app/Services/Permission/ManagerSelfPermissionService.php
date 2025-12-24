<?php

namespace App\Services\Permission;

use App\Models\User;
use Spatie\Permission\Models\Permission;

class ManagerSelfPermissionService
{
    /**
     * Manager'ın kendisine ekleyebileceği permission'ları getirir
     */
    public function getAssignableForSelf(User $manager)
    {
        return Permission::query()
            ->where('is_assignable', true)
            ->where('assign_level', 'manager')
            ->pluck('name');
    }

    /**
     * Manager kendisine permission ekler
     */
    public function assignToSelf(User $manager, array $permissions): void
    {
        $allowed = Permission::query()
            ->where('is_assignable', true)
            ->where('assign_level', 'manager')
            ->whereIn('name', $permissions)
            ->pluck('name')
            ->toArray();

        // sadece allowed olanları ekle
        foreach ($allowed as $perm) {
            if (!$manager->hasPermissionTo($perm)) {
                $manager->givePermissionTo($perm);
            }
        }
    }

    /**
     * Manager kendisinden permission kaldırır
     */
    public function revokeFromSelf(User $manager, array $permissions): void
    {
        $allowed = Permission::query()
            ->where('is_assignable', true)
            ->where('assign_level', 'manager')
            ->whereIn('name', $permissions)
            ->pluck('name')
            ->toArray();

        foreach ($allowed as $perm) {
            if ($manager->hasPermissionTo($perm)) {
                $manager->revokePermissionTo($perm);
            }
        }
    }
}
