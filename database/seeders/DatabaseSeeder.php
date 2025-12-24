<?php

namespace Database\Seeders;

use App\Models\Subject;
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
            GradeSeeder::class,
            PackageSeeder::class,
            PackageWeekGradeRuleSeeder::class,
            SubjectSeeder::class,
            SystemChatGroupSeeder::class,
            PermissionSeeder::class,
            DefaultPermissionSeeder::class,
            AssignLevelPermissionSeeder::class,
        ]);
    }
}
