<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;

class AdminUserSeeder extends Seeder
{
    public function run()
    {
        // Rolleri oluştur
        $roles = ['admin', 'manager'];

        foreach ($roles as $role) {
            Role::firstOrCreate([
                'name' => $role,
                'guard_name' => 'sanctum'
            ]);
        }

        // Admin oluştur
        $admin = User::firstOrCreate(
            ['email' => 'root@root.com'],
            [
                'name' => 'Super Admin',
                'userName' => 'root',
                'password' => bcrypt('root'),
                'role' => "admin",
            ]
        );
        $admin->syncRoles(['admin']);

        // Manager oluştur
        $manager = User::firstOrCreate(
            ['email' => 'manager@manager.com'],
            [
                'name' => 'manager manager',
                'userName' => 'manager',
                'password' => bcrypt('manager'),
                'role' => 'manager',
            ]
        );
        $manager->syncRoles(['manager']);
    }
}
