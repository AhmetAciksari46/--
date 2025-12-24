<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;


class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            [
                'name' => 'classes.view',
                'guard_name' => 'sanctum',
                'is_default' => true,
                'is_assignable' => false,
            ],
            [
                'name' => 'classes.create',
                'guard_name' => 'sanctum',
                'is_default' => false,
                'is_assignable' => true,
            ],
            [
                'name' => 'permissions.assign',
                'guard_name' => 'sanctum',
                'is_default' => false,
                'is_assignable' => false,
            ],
        ];

        foreach ($items as $data) {
            Permission::updateOrCreate(
                [
                    'name' => $data['name'],
                    'guard_name' => $data['guard_name'],
                ],
                $data
            );
        }
    }
}
