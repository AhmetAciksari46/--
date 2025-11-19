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

            // USER
            'user.create',
            'user.update',
            'user.delete',
            'user.view',
            'user.view.list',
            // SCHOOL
            'school.create',
            'school.update',
            'school.delete',
            'school.view',
            'school.view.list',

            // CLASS
            'classmodel.create',
            'classmodel.update',
            'classmodel.delete',
            'classmodel.view',
            'classmodel.view.list',

            // STUDENT
            'student.create',
            'student.update',
            'student.delete',
            'student.view',
            'student.view.list',

            // TEACHER
            'teacher.create',
            'teacher.update',
            'teacher.delete',
            'teacher.view',
            'teacher.view.list',

            // WORKER
            'worker.create',
            'worker.update',
            'worker.delete',
            'worker.view',
            'worker.view.list',

            // SETTINGS
            'profile.update',
            'settings.update',

            // PACKAGE
            'package.create',
            'package.update',
            'package.delete',
            'package.view',
            'package.view.list',

            'subscription.create',
            'subscription.update',
            'subscription.delete',
            'subscription.view',
            'subscription.view.list',

            'grade.create',
            'grade.update',
            'grade.delete',
            'grade.view',
            'grade.view.list',

            'teacher.permissions.update',
            'teacher.permissions.view',
            'teacher.available.permissions.view',
            'teacher.reset.password',

        ];


        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'sanctum',   // 👈 KRİTİK
            ]);
        }

        $admin = Role::firstOrCreate(
            ['name' => 'admin', 'guard_name' => 'sanctum']
        );
        $manager = Role::firstOrCreate(
            ['name' => 'manager', 'guard_name' => 'sanctum']
        );
        $teacher = Role::firstOrCreate(
            ['name' => 'teacher', 'guard_name' => 'sanctum']
        );
        $individualstudent = Role::firstOrCreate(
            ['name' => 'individualstudent', 'guard_name' => 'sanctum']
        );
        $schoolstudent = Role::firstOrCreate(
            ['name' => 'schoolstudent', 'guard_name' => 'sanctum']
        );
        $worker = Role::firstOrCreate(
            ['name' => 'worker', 'guard_name' => 'sanctum']
        );

        // Role => Permission eşleştirmeleri
        $admin->syncPermissions(Permission::where('guard_name', 'sanctum')->get());

        // $manager->givePermissionTo([
        //     'organization.edit',
        //     'profile.update',

        //     'class.create',
        //     'class.edit',
        //     'class.delete',
        //     'class.view',
        //     'class.view.list',

        //     'student.view',
        //     'student.view.list',
        //     'student.create',
        //     'student.edit',
        //     'student.delete',

        //     'worker.create',
        //     'worker.edit',
        //     'worker.delete',
        //     'worker.view.list',
        //     'worker.view',

        //     'teacher.create',
        //     'teacher.edit',
        //     'teacher.delete',
        //     'teacher.view',
        //     'teacher.view.list',
        //     'school.create',
        //     'school.update',
        //     'school.delete',
        //     'school.view',
        //     'organization.view',

        // ]);
        $manager->syncPermissions([
            'profile.update',

            // CLASS
            'classmodel.create',
            'classmodel.update',
            'classmodel.delete',
            'classmodel.view',
            'classmodel.view.list',

            // STUDENT
            'student.create',
            'student.update',
            'student.delete',
            'student.view',
            'student.view.list',

            // WORKER
            'worker.create',
            'worker.update',
            'worker.delete',
            'worker.view',
            'worker.view.list',

            // TEACHER
            'teacher.create',
            'teacher.update',
            'teacher.delete',
            'teacher.view',
            'teacher.view.list',

            // SCHOOL
            'school.view',
            'school.update',
            //permissiosnlar
            'teacher.permissions.update',
            'teacher.permissions.view',
            'teacher.available.permissions.view',
            'teacher.reset.password',
        ]);



        $teacher->syncPermissions([
            'profile.update',

            // TEACHER Görevleri
            'student.view',
            'student.view.list',
            'classmodel.view',
            'classmodel.view.list',

            // Eğer öğretmene CRUD yetkisi vermek istersen:
            // 'student.create', 'student.update', 'student.delete',
            // 'class.create', 'class.update', 'class.delete',
        ]);


        $individualstudent->givePermissionTo([
            'profile.update',
        ]);
        // $schoolstudent->givePermissionTo([
        //     'profile.update',
        //     'student.view.list',
        // ]);
        // ---- SCHOOL STUDENT ----
        $schoolstudent->syncPermissions([
            'profile.update',
            'student.view.list',
        ]);
        $worker->givePermissionTo([
            'profile.update',
        ]);
    }
}
