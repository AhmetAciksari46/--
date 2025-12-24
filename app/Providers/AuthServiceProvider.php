<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use App\Policies\TeacherPolicy;
use App\Models\ClassModel;
use App\Models\Attendance;
use App\Models\LessonSession;
use App\Models\ClassSchedule;
use App\Models\SchoolHasGrade;

use App\Policies\LessonSessionPolicy;
use App\Policies\ClassModelPolicy;
use App\Policies\SchoolStudentPolicy;
use App\Models\SchoolSession;
use App\Models\SchoolStudentProfile;
use App\Policies\SchoolSessionPolicy;
use App\Models\StudentCurriculumOverride;
use App\Policies\StudentCurriculumOverridePolicy;
use App\Policies\ClassSchedulePolicy;
use App\Policies\SchoolPolicy;
use App\Models\School;
use App\Policies\AttendancePolicy;
use App\Policies\SchoolHasGradePolicy;
use App\Policies\MessagePolicy;
use App\Policies\CommentPolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     */
    protected $policies = [
        User::class => TeacherPolicy::class,
        ClassModel::class => ClassModelPolicy::class,
        SchoolStudentProfile::class => SchoolStudentPolicy::class,
        SchoolSession::class => SchoolSessionPolicy::class,
        StudentCurriculumOverride::class => StudentCurriculumOverridePolicy::class,
        Attendance::class => AttendancePolicy::class,
        LessonSession::class => LessonSessionPolicy::class,
        ClassSchedule::class => ClassSchedulePolicy::class,
        SchoolHasGrade::class => SchoolHasGradePolicy::class,
        School::class => SchoolPolicy::class,




    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
        Gate::before(function ($user, $ability) {
            if ($user->role === 'admin') {
                return true;
            }
        });
        // Ek gate tanımları gerekiyorsa buraya

        Gate::define('send-message', [MessagePolicy::class, 'send']);
        Gate::define('create-comment', [CommentPolicy::class, 'create']);

        // örn: Gate::define('admin-only', fn($user) => $user->role === 'admin');
    }
}
