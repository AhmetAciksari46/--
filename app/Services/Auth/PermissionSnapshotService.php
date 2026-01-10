<?php

namespace App\Services\Auth;

use App\Models\User;
use Spatie\Permission\PermissionRegistrar;

class PermissionSnapshotService
{
    public function build(User $user): array
    {
        // ✅ Cache temizle (özellikle login gibi kritik yerde)
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // ✅ Direkt Spatie'den tüm izinleri al (direct + role)
        return $user->getAllPermissions()
            ->pluck('name')
            ->unique()
            ->values()
            ->toArray();
    }
}
