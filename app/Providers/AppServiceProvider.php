<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \App\Models\ClassModel::observe(\App\Observers\ClassModelObserver::class);
        \App\Models\User::observe(\App\Observers\UserObserver::class);
        \App\Models\School::observe(\App\Observers\SchoolObserver::class);
        \App\Models\SchoolStudentProfile::observe(\App\Observers\SchoolStudentProfileObserver::class);
        \App\Models\TeacherProfile::observe(\App\Observers\TeacherProfileObserver::class);
        \App\Models\ManagerProfile::observe(\App\Observers\ManagerProfileObserver::class);
    }
}
