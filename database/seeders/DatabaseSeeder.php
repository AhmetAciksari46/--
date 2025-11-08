<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        $this->call([
            RolePermissionSeeder::class,
            AdminUserSeeder::class,
            BranchSeeder::class,
            SubjectSeeder::class,
            PackageSeeder::class,
            PackageWeekGradeRuleSeeder::class,
            PackageWeekSubjectRuleSeeder::class,
        ]);
    }
}
