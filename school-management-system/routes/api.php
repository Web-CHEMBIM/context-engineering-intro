<?php

use App\Http\Controllers\Api\V1\AcademicYearController;
use App\Http\Controllers\Api\V1\SchoolClassController;
use App\Http\Controllers\Api\V1\StudentController;
use App\Http\Controllers\Api\V1\SubjectController;
use App\Http\Controllers\Api\V1\TeacherController;
use App\Http\Controllers\Api\V1\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public API routes
Route::get('/health', function () {
    return response()->json([
        'status' => 'healthy',
        'version' => '1.0.0',
        'timestamp' => now()->toISOString(),
        'environment' => config('app.env'),
    ]);
});

// API Version 1 routes
Route::prefix('v1')->group(function () {
    
    // Authentication routes (if needed for API)
    Route::post('/login', function (Request $request) {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (!auth()->attempt($credentials)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $user = auth()->user();
        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
            'token_type' => 'Bearer',
        ]);
    });

    // Protected API routes
    Route::middleware(['auth:sanctum'])->group(function () {
        
        // User profile
        Route::get('/profile', function (Request $request) {
            return response()->json($request->user()->load(['roles', 'permissions']));
        });

        Route::post('/logout', function (Request $request) {
            $request->user()->currentAccessToken()->delete();
            return response()->json(['message' => 'Logged out successfully']);
        });

        // Users API
        Route::apiResource('users', UserController::class);
        Route::post('users/{user}/restore', [UserController::class, 'restore']);
        Route::get('users/{user}/permissions', [UserController::class, 'permissions']);

        // Students API
        Route::apiResource('students', StudentController::class);
        Route::get('students/{student}/performance', [StudentController::class, 'performance']);

        // Teachers API
        Route::apiResource('teachers', TeacherController::class);

        // Classes API
        Route::apiResource('classes', SchoolClassController::class);

        // Subjects API
        Route::apiResource('subjects', SubjectController::class);

        // Academic Years API
        Route::apiResource('academic-years', AcademicYearController::class);
        Route::post('academic-years/{academicYear}/set-current', [AcademicYearController::class, 'setCurrent']);

        // Dashboard API endpoints
        Route::prefix('dashboard')->group(function () {
            Route::get('/stats', function () {
                return response()->json([
                    'total_users' => \App\Models\User::where('is_active', true)->count(),
                    'total_students' => \App\Models\Student::whereHas('user', function($q) { $q->where('is_active', true); })->count(),
                    'total_teachers' => \App\Models\Teacher::whereHas('user', function($q) { $q->where('is_active', true); })->count(),
                    'total_classes' => \App\Models\SchoolClass::where('is_active', true)->count(),
                    'total_subjects' => \App\Models\Subject::where('is_active', true)->count(),
                    'current_academic_year' => \App\Models\AcademicYear::where('is_current', true)->first()?->name,
                ]);
            });

            Route::get('/recent-activities', function () {
                $recentStudents = \App\Models\Student::with('user')
                    ->whereHas('user', function($q) { $q->where('is_active', true); })
                    ->latest()
                    ->take(10)
                    ->get();

                return response()->json([
                    'recent_enrollments' => $recentStudents->map(function($student) {
                        return [
                            'id' => $student->id,
                            'name' => $student->user->name,
                            'student_id' => $student->student_id,
                            'enrolled_at' => $student->created_at->format('Y-m-d H:i:s'),
                        ];
                    })
                ]);
            });
        });

        // Bulk operations
        Route::prefix('bulk')->group(function () {
            Route::post('/students/import', function (Request $request) {
                // Placeholder for CSV/Excel import
                return response()->json(['message' => 'Bulk import not yet implemented'], 501);
            });

            Route::post('/teachers/assign-subjects', function (Request $request) {
                // Placeholder for bulk subject assignment
                return response()->json(['message' => 'Bulk assignment not yet implemented'], 501);
            });
        });

        // Reports API
        Route::prefix('reports')->group(function () {
            Route::get('/enrollment', function (Request $request) {
                $academicYearId = $request->get('academic_year_id');
                $query = \App\Models\Student::query();
                
                if ($academicYearId) {
                    $query->where('academic_year_id', $academicYearId);
                }
                
                return response()->json([
                    'total_enrolled' => $query->count(),
                    'by_grade' => $query->join('student_class', 'students.id', '=', 'student_class.student_id')
                                       ->join('school_classes', 'student_class.school_class_id', '=', 'school_classes.id')
                                       ->selectRaw('school_classes.grade_level, COUNT(*) as count')
                                       ->groupBy('school_classes.grade_level')
                                       ->get(),
                ]);
            });

            Route::get('/performance', function () {
                // Mock performance data
                return response()->json([
                    'overall_average' => rand(75, 90),
                    'by_subject' => collect(['Math', 'Science', 'English', 'History'])->map(function($subject) {
                        return [
                            'subject' => $subject,
                            'average' => rand(70, 95),
                        ];
                    }),
                ]);
            });
        });
    });
});

// API Documentation route (if using tools like Swagger)
Route::get('/docs', function () {
    return response()->json([
        'message' => 'API Documentation',
        'version' => '1.0.0',
        'endpoints' => [
            'Authentication' => [
                'POST /api/v1/login',
                'POST /api/v1/logout',
                'GET /api/v1/profile',
            ],
            'Users' => [
                'GET /api/v1/users',
                'POST /api/v1/users',
                'GET /api/v1/users/{id}',
                'PUT /api/v1/users/{id}',
                'DELETE /api/v1/users/{id}',
            ],
            'Students' => [
                'GET /api/v1/students',
                'POST /api/v1/students',
                'GET /api/v1/students/{id}',
                'PUT /api/v1/students/{id}',
                'DELETE /api/v1/students/{id}',
                'GET /api/v1/students/{id}/performance',
            ],
            'Teachers' => [
                'GET /api/v1/teachers',
                'POST /api/v1/teachers',
                'GET /api/v1/teachers/{id}',
                'PUT /api/v1/teachers/{id}',
                'DELETE /api/v1/teachers/{id}',
            ],
            'Classes' => [
                'GET /api/v1/classes',
                'POST /api/v1/classes',
                'GET /api/v1/classes/{id}',
                'PUT /api/v1/classes/{id}',
                'DELETE /api/v1/classes/{id}',
            ],
            'Subjects' => [
                'GET /api/v1/subjects',
                'POST /api/v1/subjects',
                'GET /api/v1/subjects/{id}',
                'PUT /api/v1/subjects/{id}',
                'DELETE /api/v1/subjects/{id}',
            ],
            'Academic Years' => [
                'GET /api/v1/academic-years',
                'POST /api/v1/academic-years',
                'GET /api/v1/academic-years/{id}',
                'PUT /api/v1/academic-years/{id}',
                'DELETE /api/v1/academic-years/{id}',
                'POST /api/v1/academic-years/{id}/set-current',
            ],
            'Dashboard' => [
                'GET /api/v1/dashboard/stats',
                'GET /api/v1/dashboard/recent-activities',
            ],
            'Reports' => [
                'GET /api/v1/reports/enrollment',
                'GET /api/v1/reports/performance',
            ],
        ],
    ]);
});