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

            'school.create',
            'school.update',
            'school.delete',
            'school.view',

            'class.create',
            'class.edit',
            'class.delete',

            'student.create',
            'student.edit',
            'student.delete',

            'worker.create',
            'worker.edit',
            'worker.delete',

            'teacher.create',
            'teacher.edit',
            'teacher.delete',

            'profile.update',
            'settings.edit',

            'package.create',
            'package.edit',
            'package.delete',
            'package.view',

            'student.view',
            'class.view',
            'teacher.view',
            'worker.view',
            'organization.view',

            'student.view.list',
            'class.view.list',
            'teacher.view.list',
            'worker.view.list',
            'organization.view.list',
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
            'class.view',
            'class.view.list',

            'student.view',
            'student.view.list',
            'student.create',
            'student.edit',
            'student.delete',

            'worker.create',
            'worker.edit',
            'worker.delete',
            'worker.view.list',
            'worker.view',

            'teacher.create',
            'teacher.edit',
            'teacher.delete',
            'teacher.view',
            'teacher.view.list',
            'school.create',
            'school.update',
            'school.delete',
            'school.view',
            'organization.view',

        ]);
        $teacher->givePermissionTo([
            'class.create',
            'class.edit',
            'class.delete',
            'student.create',
            'student.edit',
            'student.delete',
            'profile.update',
            'student.view',
            'class.view',
            'student.view.list',
            'class.view.list',

        ]);
        $individualstudent->givePermissionTo([
            'profile.update',
        ]);
        $schoolstudent->givePermissionTo([
            'profile.update',
            'student.view.list',
        ]);
        $worker->givePermissionTo([
            'profile.update',
        ]);
    }
}
