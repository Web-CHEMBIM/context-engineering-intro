<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Models\User;
use App\Models\SchoolClass;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Teacher Management Controller
 * 
 * Handles CRUD operations for teachers with subject and class assignments
 */
class TeacherController extends Controller
{
    /**
     * Display a listing of teachers.
     */
    public function index(Request $request)
    {
        $query = Teacher::with(['user', 'subjects', 'schoolClasses'])
            ->whereHas('user', function($q) {
                $q->where('is_active', true);
            })
            ->orderBy('created_at', 'desc');

        // Filter by department
        if ($request->filled('department')) {
            $query->where('department', $request->department);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search by name, teacher ID, or email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('teacher_id', 'like', "%{$search}%")
                  ->orWhere('employee_id', 'like', "%{$search}%")
                  ->orWhereHas('user', function($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%")
                               ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $teachers = $query->paginate(20);
        
        // Get unique departments for filter
        $departments = Teacher::distinct()->pluck('department')->filter()->sort();

        $breadcrumbs = [
            ['title' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['title' => 'Teachers', 'url' => route('teachers.index')]
        ];

        return view('admin.teachers.index', compact('teachers', 'departments', 'breadcrumbs'));
    }

    /**
     * Show the form for creating a new teacher.
     */
    public function create()
    {
        $subjects = Subject::orderBy('name')->get();
        $classes = SchoolClass::with('academicYear')->orderBy('grade_level')->orderBy('name')->get();

        $breadcrumbs = [
            ['title' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['title' => 'Teachers', 'url' => route('teachers.index')],
            ['title' => 'Create Teacher', 'url' => route('teachers.create')]
        ];

        return view('admin.teachers.create', compact('subjects', 'classes', 'breadcrumbs'));
    }

    /**
     * Store a newly created teacher in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            // User fields
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'phone' => ['nullable', 'string', 'max:20'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'gender' => ['nullable', 'string', 'in:male,female,other'],
            'address' => ['nullable', 'string', 'max:500'],
            
            // Teacher specific fields
            'teacher_id' => ['required', 'string', 'max:20', 'unique:teachers,teacher_id'],
            'employee_id' => ['required', 'string', 'max:20', 'unique:teachers,employee_id'],
            'department' => ['nullable', 'string', 'max:100'],
            'salary' => ['nullable', 'numeric', 'min:0'],
            'hire_date' => ['required', 'date'],
            'qualification' => ['nullable', 'string', 'max:500'],
            'experience_years' => ['nullable', 'integer', 'min:0'],
            'specialization' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'string', 'in:active,inactive,on_leave,terminated'],
            
            // Assignments
            'subjects' => ['array'],
            'subjects.*' => ['exists:subjects,id'],
            'classes' => ['array'],
            'classes.*' => ['exists:school_classes,id']
        ]);

        DB::transaction(function () use ($request) {
            // Create user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'phone' => $request->phone,
                'date_of_birth' => $request->date_of_birth,
                'gender' => $request->gender,
                'address' => $request->address,
                'is_active' => true,
                'email_verified_at' => now(),
            ]);

            // Assign Teacher role
            $user->assignRole('Teacher');

            // Create teacher profile
            $teacher = Teacher::create([
                'user_id' => $user->id,
                'teacher_id' => $request->teacher_id,
                'employee_id' => $request->employee_id,
                'department' => $request->department,
                'salary' => $request->salary,
                'hire_date' => $request->hire_date,
                'qualification' => $request->qualification,
                'experience_years' => $request->experience_years,
                'specialization' => $request->specialization,
                'status' => $request->status
            ]);

            // Assign subjects
            if ($request->filled('subjects')) {
                $teacher->subjects()->sync($request->subjects);
            }

            // Assign classes
            if ($request->filled('classes')) {
                $teacher->schoolClasses()->sync($request->classes);
            }
        });

        return redirect()->route('teachers.index')
            ->with('success', 'Teacher created successfully.');
    }

    /**
     * Display the specified teacher.
     */
    public function show(Teacher $teacher)
    {
        $teacher->load([
            'user',
            'subjects',
            'schoolClasses.students',
            'primarySubjects' // Subjects where this teacher is primary
        ]);

        // Teaching statistics
        $stats = [
            'total_subjects' => $teacher->subjects->count(),
            'total_classes' => $teacher->schoolClasses->count(),
            'total_students' => $teacher->schoolClasses->sum(function($class) {
                return $class->students->count();
            }),
            'teaching_experience' => $teacher->experience_years ?? 0,
            'tenure_at_school' => $teacher->hire_date->diffInYears(now())
        ];

        // Workload analysis
        $workload = [
            'classes_per_week' => $teacher->schoolClasses->count() * 5, // Assuming 5 days a week
            'subjects_taught' => $teacher->subjects->count(),
            'average_class_size' => $teacher->schoolClasses->count() > 0 ? 
                round($stats['total_students'] / $teacher->schoolClasses->count(), 1) : 0
        ];

        // Performance metrics (mock data)
        $performance = [
            'student_satisfaction' => 4.2,
            'class_average_grade' => 78.5,
            'attendance_rate' => 96.8
        ];

        $breadcrumbs = [
            ['title' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['title' => 'Teachers', 'url' => route('teachers.index')],
            ['title' => $teacher->user->name, 'url' => route('teachers.show', $teacher)]
        ];

        return view('admin.teachers.show', compact('teacher', 'stats', 'workload', 'performance', 'breadcrumbs'));
    }

    /**
     * Show the form for editing the specified teacher.
     */
    public function edit(Teacher $teacher)
    {
        $teacher->load(['user', 'subjects', 'schoolClasses']);
        
        $subjects = Subject::orderBy('name')->get();
        $classes = SchoolClass::with('academicYear')->orderBy('grade_level')->orderBy('name')->get();

        $breadcrumbs = [
            ['title' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['title' => 'Teachers', 'url' => route('teachers.index')],
            ['title' => $teacher->user->name, 'url' => route('teachers.show', $teacher)],
            ['title' => 'Edit', 'url' => route('teachers.edit', $teacher)]
        ];

        return view('admin.teachers.edit', compact('teacher', 'subjects', 'classes', 'breadcrumbs'));
    }

    /**
     * Update the specified teacher in storage.
     */
    public function update(Request $request, Teacher $teacher)
    {
        $request->validate([
            // User fields
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($teacher->user_id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'phone' => ['nullable', 'string', 'max:20'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'gender' => ['nullable', 'string', 'in:male,female,other'],
            'address' => ['nullable', 'string', 'max:500'],
            
            // Teacher specific fields
            'teacher_id' => ['required', 'string', 'max:20', Rule::unique('teachers')->ignore($teacher->id)],
            'employee_id' => ['required', 'string', 'max:20', Rule::unique('teachers')->ignore($teacher->id)],
            'department' => ['nullable', 'string', 'max:100'],
            'salary' => ['nullable', 'numeric', 'min:0'],
            'hire_date' => ['required', 'date'],
            'qualification' => ['nullable', 'string', 'max:500'],
            'experience_years' => ['nullable', 'integer', 'min:0'],
            'specialization' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'string', 'in:active,inactive,on_leave,terminated'],
            
            // Assignments
            'subjects' => ['array'],
            'subjects.*' => ['exists:subjects,id'],
            'classes' => ['array'],
            'classes.*' => ['exists:school_classes,id']
        ]);

        DB::transaction(function () use ($request, $teacher) {
            // Update user
            $userData = [
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'date_of_birth' => $request->date_of_birth,
                'gender' => $request->gender,
                'address' => $request->address,
                'is_active' => $request->status === 'active',
            ];

            if ($request->filled('password')) {
                $userData['password'] = Hash::make($request->password);
            }

            $teacher->user->update($userData);

            // Update teacher profile
            $teacher->update([
                'teacher_id' => $request->teacher_id,
                'employee_id' => $request->employee_id,
                'department' => $request->department,
                'salary' => $request->salary,
                'hire_date' => $request->hire_date,
                'qualification' => $request->qualification,
                'experience_years' => $request->experience_years,
                'specialization' => $request->specialization,
                'status' => $request->status
            ]);

            // Update subject assignments
            $teacher->subjects()->sync($request->input('subjects', []));

            // Update class assignments
            $teacher->schoolClasses()->sync($request->input('classes', []));
        });

        return redirect()->route('teachers.show', $teacher)
            ->with('success', 'Teacher updated successfully.');
    }

    /**
     * Remove the specified teacher from storage.
     */
    public function destroy(Teacher $teacher)
    {
        // Check if teacher is assigned as primary teacher to any subjects or classes
        $primarySubjects = Subject::where('primary_teacher_id', $teacher->id)->count();
        $primaryClasses = SchoolClass::where('class_teacher_id', $teacher->id)->count();

        if ($primarySubjects > 0 || $primaryClasses > 0) {
            return redirect()->route('teachers.index')
                ->with('error', 'Cannot delete teacher who is assigned as primary teacher to subjects or classes. Please reassign first.');
        }

        // Soft delete by deactivating the user instead of hard delete
        DB::transaction(function () use ($teacher) {
            // Deactivate user
            $teacher->user->update(['is_active' => false]);
            
            // Update teacher status
            $teacher->update(['status' => 'terminated']);
            
            // Remove from all assignments
            $teacher->subjects()->detach();
            $teacher->schoolClasses()->detach();
        });

        return redirect()->route('teachers.index')
            ->with('success', 'Teacher deactivated successfully.');
    }

    /**
     * Assign subjects to teacher.
     */
    public function assignSubjects(Request $request, Teacher $teacher)
    {
        $request->validate([
            'subjects' => ['required', 'array'],
            'subjects.*' => ['exists:subjects,id']
        ]);

        $teacher->subjects()->sync($request->subjects);

        return redirect()->route('teachers.show', $teacher)
            ->with('success', 'Subjects assigned successfully.');
    }

    /**
     * Assign classes to teacher.
     */
    public function assignClasses(Request $request, Teacher $teacher)
    {
        $request->validate([
            'classes' => ['required', 'array'],
            'classes.*' => ['exists:school_classes,id']
        ]);

        $teacher->schoolClasses()->sync($request->classes);

        return redirect()->route('teachers.show', $teacher)
            ->with('success', 'Classes assigned successfully.');
    }

    /**
     * Update teacher workload and schedule.
     */
    public function updateWorkload(Request $request, Teacher $teacher)
    {
        $request->validate([
            'max_classes_per_day' => ['nullable', 'integer', 'min:1', 'max:10'],
            'preferred_subjects' => ['array'],
            'preferred_subjects.*' => ['exists:subjects,id'],
            'availability' => ['nullable', 'json']
        ]);

        $teacher->update([
            'max_classes_per_day' => $request->max_classes_per_day,
            'preferred_subjects' => $request->input('preferred_subjects', []),
            'availability' => $request->availability
        ]);

        return redirect()->route('teachers.show', $teacher)
            ->with('success', 'Workload preferences updated successfully.');
    }

    /**
     * Get teachers by department (AJAX endpoint).
     */
    public function getByDepartment(Request $request)
    {
        $department = $request->get('department');
        
        $teachers = Teacher::with('user')
            ->where('department', $department)
            ->where('status', 'active')
            ->whereHas('user', function($q) {
                $q->where('is_active', true);
            })
            ->get()
            ->map(function($teacher) {
                return [
                    'id' => $teacher->id,
                    'name' => $teacher->user->name,
                    'teacher_id' => $teacher->teacher_id,
                    'specialization' => $teacher->specialization
                ];
            });

        return response()->json($teachers);
    }

    /**
     * Generate teacher workload report.
     */
    public function workloadReport()
    {
        $teachers = Teacher::with(['user', 'subjects', 'schoolClasses.students'])
            ->where('status', 'active')
            ->whereHas('user', function($q) {
                $q->where('is_active', true);
            })
            ->get()
            ->map(function($teacher) {
                return [
                    'teacher' => $teacher,
                    'total_classes' => $teacher->schoolClasses->count(),
                    'total_subjects' => $teacher->subjects->count(),
                    'total_students' => $teacher->schoolClasses->sum(function($class) {
                        return $class->students->count();
                    }),
                    'workload_score' => $this->calculateWorkloadScore($teacher)
                ];
            })
            ->sortByDesc('workload_score');

        $breadcrumbs = [
            ['title' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['title' => 'Teachers', 'url' => route('teachers.index')],
            ['title' => 'Workload Report', 'url' => '#']
        ];

        return view('admin.teachers.workload-report', compact('teachers', 'breadcrumbs'));
    }

    /**
     * Calculate teacher workload score.
     */
    private function calculateWorkloadScore(Teacher $teacher)
    {
        $classCount = $teacher->schoolClasses->count();
        $subjectCount = $teacher->subjects->count();
        $studentCount = $teacher->schoolClasses->sum(function($class) {
            return $class->students->count();
        });

        // Simple workload calculation (can be made more sophisticated)
        return ($classCount * 2) + ($subjectCount * 1.5) + ($studentCount * 0.1);
    }
}