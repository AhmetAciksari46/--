<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run()
    {
        // Önce cache temizle
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Permissions
        $permissions = [
            'user.create',
            'user.edit',
            'user.delete',
            'organization.create',
            'organization.edit',
            'organization.delete',
            'settings.edit',
            'class.create',
            'class.edit',
            'class.delete',
            'student.create',
            'student.edit',
            'student.delete',
            'profile.update',

        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $admin = Role::firstOrCreate(['name' => 'admin']);
        $manager = Role::firstOrCreate(['name' => 'manager']);
        $teacher = Role::firstOrCreate(['name' => 'teacher']);
        $individualstudent = Role::firstOrCreate(['name' => 'individualstudent']);
        $schoolstudent = Role::firstOrCreate(['name' => 'schoolschstudent']);
        $worker = Role::firstOrCreate(['name' => 'worker']);

        // Role => Permission eşleştirmeleri
        $admin->givePermissionTo(Permission::all()); // admin her şeye sahip

        $manager->givePermissionTo([
            'organization.edit',
            'profile.update',
            'class.create',
            'class.edit',
            'class.delete',
            'student.create',
            'student.edit',
            'student.delete'
        ]);
         $teacher->givePermissionTo([
             'class.create',
            'class.edit',
            'class.delete',
            'student.create',
            'student.edit',
            'student.delete',
             'profile.update',

        ]);
        $individualstudent->givePermissionTo([
            'profile.update',
        ]);
         $schoolstudent->givePermissionTo([
            'profile.update',
        ]);
        $worker->givePermissionTo([
            'profile.update',
        ]);
    }
}
