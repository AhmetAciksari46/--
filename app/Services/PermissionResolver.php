<?php

namespace App\Services;

use App\Models\User;
use Spatie\Permission\Models\Permission;

class PermissionResolver
{
    /**
     * Kullanıcının effective permission listesini döner
     *
     * @return string[]
     */
    public function resolve(User $user): array
    {
        // 1️⃣ Default permission'lar (Spatie permissions tablosundan)
        $defaultPermissions = Permission::query()
            ->where('is_default', true)
            ->pluck('name')
            ->toArray();

        // 2️⃣ Kullanıcıya atanmış permission'lar (Spatie üzerinden)
        $userPermissions = $user
            ->getAllPermissions()
            ->pluck('name')
            ->toArray();

        // 3️⃣ Merge + unique
        return collect($defaultPermissions)
            ->merge($userPermissions)
            ->unique()
            ->values()
            ->toArray();
    }
}
