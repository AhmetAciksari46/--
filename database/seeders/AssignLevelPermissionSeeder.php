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
                'classmodel.create',
                'classmodel.update',
                'classmodel.delete',
                'teacher.view.detail',
                'manager.view.detail',
                'studentpreregistration.create',
                'studentpreregistration.update',
                'studentpreregistration.approve',
                'studentpreregistration.cancel',
                'parentbirthdays.view.detail',
                'teacherbirthdays.view.detail',
            ])
            ->update([
                'is_assignable' => true,
                'assign_level' => 'manager',
            ]);

        // SADECE admin'in verebileceği permission'lar
        Permission::query()
            ->whereIn('name', [
                'package.create',
                'package.update',
                'package.delete',
                'school.delete',
                'school.create',
            ])
            ->update([
                'is_assignable' => true,
                'assign_level' => 'admin',
            ]);
    }
}
