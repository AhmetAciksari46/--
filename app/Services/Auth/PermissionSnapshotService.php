<?php

namespace App\Services\Auth;

use App\Models\User;
use Spatie\Permission\Models\Permission;

class PermissionSnapshotService
{
    public function build(User $user): array
    {
        // 1️⃣ Default permission’lar
        $defaultPermissions = Permission::query()
            ->where('is_default', true)
            ->pluck('name')
            ->toArray();

        // 2️⃣ Role + User permission’ları (Spatie)
        $spatiePermissions = $user
            ->getAllPermissions()
            ->pluck('name')
            ->toArray();

        // 3️⃣ Merge + unique
        return array_values(array_unique([
            ...$defaultPermissions,
            ...$spatiePermissions,
        ]));
    }
}
