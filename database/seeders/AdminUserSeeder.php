<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class AdminUserSeeder extends Seeder
{
    public function run()
    {
        $admin = User::firstOrCreate(
            ['email' => 'root@root.com'],
            [
                'name' => 'Super Admin',
                'userName' => 'root',
                'password' => bcrypt('root'),
                'role' => "admin",
            ]
        );

        $admin->syncRoles(['admin']);   // 👈 guard sanctum ile admin rolü

        //--------------------------------------------------------
        $manager = User::firstOrCreate(
            ['email' => 'manager@manager.com'],
            [
                'name' => 'manager manager',
                'userName' => 'manager',
                'password' => bcrypt('manager'),
                'role' => 'manager',
            ]
        );
        $manager->syncRoles(['manager']);   // 👈 guard sanctum ile admin rolü

    }
}
