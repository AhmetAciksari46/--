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
            ]
        );

        $admin->assignRole('admin');
        //--------------------------------------------------------
        $manager = User::firstOrCreate(
            ['email' => 'manager@manager.com'],
            [
                'name' => 'manager manager',
                'userName' => 'manager',
                'password' => bcrypt('manager'),
            ]
        );
        $manager->assignRole('manager');
    }
}
