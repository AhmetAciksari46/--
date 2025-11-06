<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use App\Policies\TeacherPolicy;
use App\Models\ClassModel;
use App\Policies\ClassModelPolicy;
use App\Policies\SchoolStudentPolicy;
use App\Models\SchoolSession;
use App\Models\SchoolStudentProfile;
use App\Policies\SchoolSessionPolicy;
use App\Models\StudentCurriculumOverride;
use App\Policies\StudentCurriculumOverridePolicy;

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
        // örn: Gate::define('admin-only', fn($user) => $user->role === 'admin');
    }
}
