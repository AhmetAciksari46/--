<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class AssignLevelPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Manager'ın verebileceği permission'lar
        Permission::query()
            ->whereIn('name', [

                'subscription.create',
                'subscription.update',
                'teacher.permissions.update',
                'teacher.reset.password',
                'teacher.permissions.remove',
                'classmodel.create',
                'classmodel.update',
                'classmodel.delete',
                'classmodel.view.list',
                'school.update', //admin ve managerlar için
                'school.view.detail', //manager ve adminler için
                'studentpreregistration.create',
                'studentpreregistration.update',
                'studentpreregistration.view',
                'studentpreregistration.approve',
                'studentpreregistration.cancel',
                'teachersubject.create',
                'teachersubject.update',
                'teachersubject.delete',
                'teachersubject.view.list',
                'studentlesson.view.list',
                'teacherlesson.view.list',
                'manager.view.detail',
                'student.create',
                'student.update',
                'student.delete',
                'student.view.list',
                'teacher.create',
                'teacher.update',
                'teacher.delete',
                'teacher.view.list', //admin ve managerlar için
                'teacher.view.detail', //admin ve managerlar için
                'attendance.create',
                'attendance.update',
                'attendance.delete',
                'attendance.view.list',
                'classschedule.create',
                'classschedule.update',
                'classschedule.delete',
                'classschedule.view.list',
                'lessonsession.create',
                'lessonsession.update',
                'lessonsession.delete',
                'lessonsession.view.list',
                'PhysicalClassroom.create',
                'PhysicalClassroom.update',
                'PhysicalClassroom.delete',
                'PhysicalClassroom.view.list',
                'schoolweek.create',
                'schoolweek.update',
                'schoolweek.delete',
                'schoolweek.view.list',
                'parentbirthdays.view.detail',
                'teacherbirthdays.view.detail',
                'user.create',
                'user.update',
                'user.delete',
                'user.view.list',
                'profile.update',
                'settings.update',

                //manager
                'managerpermission.create',
                'managerpermission.update',
                'managerpermission.view.list',
                'managerpermission.delete',
                'managerpermission.view',
                //user
                'userpermission.create',
                'userpermission.update',
                'userpermission.view.list',
                'userpermission.delete',
                'userpermission.view',
                'physicalclass.create',
                'physicalclass.view.list',
                'physicalclass.update',
                'physicalclass.delete',
                'physicalclass.view',
                'branch.view.list',
                'subject.view.list',
                'academicyear.view.list',

            ])
            ->update([
                'is_assignable' => true,
                'assign_level' => 'manager',
            ]);

        // SADECE admin'in verebileceği permission'lar
        Permission::query()
            ->whereIn('name', [
                'grade.view.list',
                'package.view.list',
                'packagegrade.view.list',
                'subscription.view.list',
            ])
            ->update([
                'is_assignable' => true,
                'assign_level' => 'admin',
            ]);
    }
}
