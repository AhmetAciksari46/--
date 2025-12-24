<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class DefaultPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // View ve list olanlar default
        Permission::query()
            ->where(function ($q) {
                $q->where('name', 'like', '%.view')
                    ->where('name', 'not like', '%.view.detail');
            })
            ->update(['is_default' => true]);

        // Profil gibi özel default'lar
        Permission::whereIn('name', [
            'profile.update',
        ])->update(['is_default' => true]);
    }
}
