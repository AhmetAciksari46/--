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
        $permissionss = [

            // BRANCH
            'branch.create',
            'branch.update',
            'branch.delete',
            'branch.view',
            'branch.view.list',

            // GRADE
            'grade.create',
            'grade.update',
            'grade.delete',
            'grade.view',
            'grade.view.list',

            //PACKAGE GRADE RULE
            'packagegrade.create',
            'packagegrade.update',
            'packagegrade.delete',
            'packagegrade.view',
            'packagegrade.view.list',

            // SUBJECT
            'subject.create',
            'subject.update',
            'subject.delete',
            'subject.view',
            'subject.view.list',

            //MANAGER
            'manager.create',
            'manager.update',
            'manager.delete',
            'manager.view',
            'manager.view.list',
            'manager.view.detail',



            // USER
            'user.create',
            'user.update',
            'user.delete',
            'user.view',
            'user.view.list',
            // SCHOOL
            'school.create',    //admin için
            'school.update', //admin ve managerlar için
            'school.delete', //admin için
            'school.view', //tüm roller için
            'school.view.detail', //manager ve adminler için
            'school.view.list', //admin için

            // TEACHER SUBJECT
            'teachersubject.create',
            'teachersubject.update',
            'teachersubject.delete',
            'teachersubject.view',
            'teachersubject.view.list',

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
            'teacher.view', //herkes için
            'teacher.view.list',    //admin ve managerlar için
            'teacher.view.detail', //admin ve managerlar için

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

            'classschedule.create',
            'classschedule.update',
            'classschedule.delete',
            'classschedule.view',
            'classschedule.view.list',
            'studentlesson.view.list',
            'teacherlesson.view.list',
            // PERMISSIONS


            'teacher.permissions.update',
            'teacher.permissions.view',
            'teacher.available.permissions.view',
            'teacher.reset.password',
            'teacher.permissions.remove',

            'classroom.create',
            'classroom.update',
            'classroom.delete',
            'classroom.view',
            'classroom.view.list',

            'lessonsession.create',
            'lessonsession.update',
            'lessonsession.delete',
            'lessonsession.view',
            'lessonsession.view.list',

            'PhysicalClassroom.create',
            'PhysicalClassroom.update',
            'PhysicalClassroom.delete',
            'PhysicalClassroom.view',
            'PhysicalClassroom.view.list',

            'attendance.create',
            'attendance.update',
            'attendance.delete',
            'attendance.view',
            'attendance.view.list',

            // SCHOOL WEEK
            'schoolweek.create',
            'schoolweek.update',
            'schoolweek.delete',
            'schoolweek.view',
            'schoolweek.view.list',

            //birthday
            'parentbirthdays.view.detail',
            'teacherbirthdays.view.detail',
            'studentbirthdays.view',
            //pre student
            'studentpreregistration.create',
            'studentpreregistration.update',
            'studentpreregistration.view',
            'studentpreregistration.approve',
            'studentpreregistration.cancel',
        ];

        $permissions = config('permissions');

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
        // $manager->syncPermissions([
        //     'profile.update',
        //     //ATTENDANCE
        //     'attendance.create',
        //     'attendance.update',
        //     'attendance.delete',
        //     'attendance.view',
        //     'attendance.view.list',
        //     // SCHOOL WEEK
        //     'schoolweek.create',
        //     'schoolweek.update',
        //     'schoolweek.delete',
        //     'schoolweek.view',
        //     'schoolweek.view.list',

        //     // PHYSICAL CLASSROOM
        //     'PhysicalClassroom.create',
        //     'PhysicalClassroom.update',
        //     'PhysicalClassroom.delete',
        //     'PhysicalClassroom.view',
        //     'PhysicalClassroom.view.list',
        //     // CLASS SCHEDULE
        //     'classschedule.create',
        //     'classschedule.update',
        //     'classschedule.delete',
        //     'classschedule.view',
        //     'classschedule.view.list',
        //     // CLASS
        //     'classmodel.create',
        //     'classmodel.update',
        //     'classmodel.delete',
        //     'classmodel.view',
        //     'classmodel.view.list',
        //     // TEACHER SUBJECT
        //     'teachersubject.create',
        //     'teachersubject.update',
        //     'teachersubject.delete',
        //     'teachersubject.view',
        //     'teachersubject.view.list',

        //     'studentlesson.view.list',
        //     'teacherlesson.view.list',
        //     // LESSON SESSION
        //     'lessonsession.create',
        //     'lessonsession.update',
        //     'lessonsession.delete',
        //     'lessonsession.view',
        //     'lessonsession.view.list',

        //     // STUDENT
        //     'student.create',
        //     'student.update',
        //     'student.delete',
        //     'student.view',
        //     'student.view.list',

        //     // WORKER
        //     'worker.create',
        //     'worker.update',
        //     'worker.delete',
        //     'worker.view',
        //     'worker.view.list',

        //     // TEACHER
        //     'teacher.create',
        //     'teacher.update',
        //     'teacher.delete',
        //     'teacher.view',
        //     'teacher.view.list',

        //     // SCHOOL
        //     'school.view',
        //     'school.update',
        //     //permissiosnlar
        //     'teacher.permissions.update',
        //     'teacher.permissions.view',
        //     'teacher.available.permissions.view',
        //     'teacher.reset.password',
        //     'classroom.create',
        //     'classroom.update',
        //     'classroom.delete',
        //     'classroom.view',
        //     'classroom.view.list'
        // ]);



        // $teacher->syncPermissions([
        //     'profile.update',

        //     // TEACHER Görevleri
        //     'student.view',
        //     'student.view.list',
        //     'classmodel.view',
        //     'classmodel.view.list',

        //     // Eğer öğretmene CRUD yetkisi vermek istersen:
        //     // 'student.create', 'student.update', 'student.delete',
        //     // 'class.create', 'class.update', 'class.delete',
        // ]);


        // $individualstudent->givePermissionTo([
        //     'profile.update',
        // ]);
        // // $schoolstudent->givePermissionTo([
        // //     'profile.update',
        // //     'student.view.list',
        // // ]);
        // // ---- SCHOOL STUDENT ----
        // $schoolstudent->syncPermissions([
        //     'profile.update',
        //     'student.view.list',
        // ]);
        // $worker->givePermissionTo([
        //     'profile.update',
        // ]);
    }
}
