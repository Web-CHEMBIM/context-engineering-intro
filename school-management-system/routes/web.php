<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Teacher\TeacherDashboardController;
use App\Http\Controllers\Student\StudentDashboardController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// Redirect root to appropriate dashboard based on authentication
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [LoginController::class, 'login']);
});

Route::post('logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// Protected Routes (Require Authentication)
Route::middleware(['auth', 'verified'])->group(function () {
    
    // Main Dashboard (Redirects to role-specific dashboard)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Profile Routes (All Authenticated Users)
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    
    // SuperAdmin & Admin Routes
    Route::middleware(['role:SuperAdmin,Admin'])->group(function () {
        Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
        
        // User Management (SuperAdmin only)
        Route::middleware(['role:SuperAdmin'])->group(function () {
            Route::resource('users', App\Http\Controllers\Admin\UserController::class);
        });
        
        // Academic Management (SuperAdmin & Admin)
        Route::resource('academic-years', App\Http\Controllers\Admin\AcademicYearController::class);
        Route::resource('classes', App\Http\Controllers\Admin\SchoolClassController::class);
        Route::resource('subjects', App\Http\Controllers\Admin\SubjectController::class);
        Route::resource('students', App\Http\Controllers\Admin\StudentController::class);
        Route::resource('teachers', App\Http\Controllers\Admin\TeacherController::class);
        
        // Settings (SuperAdmin & Admin)
        Route::get('/settings', [App\Http\Controllers\Admin\SettingsController::class, 'index'])->name('settings');
        Route::patch('/settings', [App\Http\Controllers\Admin\SettingsController::class, 'update'])->name('settings.update');
    });
    
    // Teacher Routes
    Route::middleware(['role:Teacher'])->group(function () {
        Route::get('/teacher/dashboard', [TeacherDashboardController::class, 'index'])->name('teacher.dashboard');
        Route::get('/teacher/classes', [TeacherDashboardController::class, 'classes'])->name('teacher.classes');
        Route::get('/teacher/students', [TeacherDashboardController::class, 'students'])->name('teacher.students');
        Route::get('/teacher/assignments', [TeacherDashboardController::class, 'assignments'])->name('teacher.assignments');
    });
    
    // Student Routes
    Route::middleware(['role:Student'])->group(function () {
        Route::get('/student/dashboard', [StudentDashboardController::class, 'index'])->name('student.dashboard');
        Route::get('/student/grades', [StudentDashboardController::class, 'grades'])->name('student.grades');
        Route::get('/student/subjects', [StudentDashboardController::class, 'subjects'])->name('student.subjects');
        Route::get('/student/assignments', [StudentDashboardController::class, 'assignments'])->name('student.assignments');
    });
});
