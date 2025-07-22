<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;

/**
 * Authorization Service Provider for School Management System
 * 
 * Configures Gates and Policies for role-based access control
 */
class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // Define model policies here if needed
        // 'App\Models\Student' => 'App\Policies\StudentPolicy',
        // 'App\Models\Teacher' => 'App\Policies\TeacherPolicy',
        // 'App\Models\SchoolClass' => 'App\Policies\SchoolClassPolicy',
        // 'App\Models\Subject' => 'App\Policies\SubjectPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // CRITICAL: SuperAdmin Bypass Pattern
        // SuperAdmin bypasses all permission checks
        Gate::before(function (User $user, $ability) {
            if ($user->hasRole('SuperAdmin')) {
                return true;
            }
        });

        // Define additional gates
        $this->defineGates();
    }

    /**
     * Define custom authorization gates.
     */
    private function defineGates(): void
    {
        // User Management Gates
        Gate::define('manage-users', function (User $user) {
            return $user->hasAnyRole(['SuperAdmin', 'Admin']);
        });

        Gate::define('create-users', function (User $user) {
            return $user->hasRole('SuperAdmin');
        });

        Gate::define('view-users', function (User $user) {
            return $user->hasAnyRole(['SuperAdmin', 'Admin']);
        });

        // Student Management Gates
        Gate::define('manage-students', function (User $user) {
            return $user->hasAnyRole(['SuperAdmin', 'Admin']);
        });

        Gate::define('view-students', function (User $user) {
            return $user->hasAnyRole(['SuperAdmin', 'Admin', 'Teacher']);
        });

        Gate::define('view-student-details', function (User $user) {
            return $user->hasAnyRole(['SuperAdmin', 'Admin', 'Teacher']);
        });

        // Teacher Management Gates
        Gate::define('manage-teachers', function (User $user) {
            return $user->hasAnyRole(['SuperAdmin', 'Admin']);
        });

        Gate::define('view-teachers', function (User $user) {
            return $user->hasAnyRole(['SuperAdmin', 'Admin']);
        });

        // Class Management Gates
        Gate::define('manage-classes', function (User $user) {
            return $user->hasAnyRole(['SuperAdmin', 'Admin']);
        });

        Gate::define('view-classes', function (User $user) {
            return $user->hasAnyRole(['SuperAdmin', 'Admin', 'Teacher']);
        });

        // Subject Management Gates
        Gate::define('manage-subjects', function (User $user) {
            return $user->hasAnyRole(['SuperAdmin', 'Admin']);
        });

        Gate::define('view-subjects', function (User $user) {
            return $user->hasAnyRole(['SuperAdmin', 'Admin', 'Teacher', 'Student']);
        });

        // Academic Year Management Gates
        Gate::define('manage-academic-years', function (User $user) {
            return $user->hasRole('SuperAdmin');
        });

        Gate::define('view-academic-years', function (User $user) {
            return $user->hasAnyRole(['SuperAdmin', 'Admin']);
        });

        // Assignment Gates
        Gate::define('manage-assignments', function (User $user) {
            return $user->hasAnyRole(['SuperAdmin', 'Admin']);
        });

        Gate::define('view-assignments', function (User $user) {
            return $user->hasAnyRole(['SuperAdmin', 'Admin', 'Teacher']);
        });

        // Dashboard Access Gates
        Gate::define('access-admin-dashboard', function (User $user) {
            return $user->hasAnyRole(['SuperAdmin', 'Admin']);
        });

        Gate::define('access-teacher-dashboard', function (User $user) {
            return $user->hasRole('Teacher');
        });

        Gate::define('access-student-dashboard', function (User $user) {
            return $user->hasRole('Student');
        });

        // Reports and Analytics Gates
        Gate::define('view-reports', function (User $user) {
            return $user->hasAnyRole(['SuperAdmin', 'Admin']);
        });

        Gate::define('view-analytics', function (User $user) {
            return $user->hasAnyRole(['SuperAdmin', 'Admin']);
        });

        // System Configuration Gates
        Gate::define('manage-system-settings', function (User $user) {
            return $user->hasRole('SuperAdmin');
        });

        Gate::define('manage-permissions', function (User $user) {
            return $user->hasRole('SuperAdmin');
        });

        // Financial Management Gates
        Gate::define('manage-fees', function (User $user) {
            return $user->hasAnyRole(['SuperAdmin', 'Admin']);
        });

        Gate::define('view-fees', function (User $user) {
            return $user->hasAnyRole(['SuperAdmin', 'Admin', 'Student']);
        });
    }
}