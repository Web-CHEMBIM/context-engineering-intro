<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\User;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Student Management Controller
 * 
 * Handles CRUD operations for students with academic data
 */
class StudentController extends Controller
{
    /**
     * Display a listing of students.
     */
    public function index(Request $request)
    {
        $query = Student::with(['user', 'academicYear', 'schoolClasses'])
            ->whereHas('user', function($q) {
                $q->where('is_active', true);
            })
            ->orderBy('created_at', 'desc');

        // Filter by academic year
        if ($request->filled('academic_year')) {
            $query->where('academic_year_id', $request->academic_year);
        }

        // Filter by class
        if ($request->filled('class')) {
            $query->whereHas('schoolClasses', function($q) use ($request) {
                $q->where('school_classes.id', $request->class);
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search by name, student ID, or email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('student_id', 'like', "%{$search}%")
                  ->orWhereHas('user', function($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%")
                               ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $students = $query->paginate(20);
        
        $academicYears = AcademicYear::where('is_active', true)->orderBy('start_date', 'desc')->get();
        $classes = SchoolClass::with('academicYear')->orderBy('grade_level')->orderBy('name')->get();

        $breadcrumbs = [
            ['title' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['title' => 'Students', 'url' => route('students.index')]
        ];

        return view('admin.students.index', compact('students', 'academicYears', 'classes', 'breadcrumbs'));
    }

    /**
     * Show the form for creating a new student.
     */
    public function create()
    {
        $academicYears = AcademicYear::where('is_active', true)->orderBy('start_date', 'desc')->get();
        $classes = SchoolClass::with('academicYear')->orderBy('grade_level')->orderBy('name')->get();
        $subjects = Subject::orderBy('name')->get();

        $breadcrumbs = [
            ['title' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['title' => 'Students', 'url' => route('students.index')],
            ['title' => 'Create Student', 'url' => route('students.create')]
        ];

        return view('admin.students.create', compact('academicYears', 'classes', 'subjects', 'breadcrumbs'));
    }

    /**
     * Store a newly created student in storage.
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
            
            // Student specific fields
            'student_id' => ['required', 'string', 'max:20', 'unique:students,student_id'],
            'admission_date' => ['required', 'date'],
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'guardian_name' => ['nullable', 'string', 'max:255'],
            'guardian_phone' => ['nullable', 'string', 'max:20'],
            'guardian_email' => ['nullable', 'email', 'max:255'],
            'emergency_contact' => ['nullable', 'string', 'max:255'],
            'medical_conditions' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', 'string', 'in:active,inactive,graduated,transferred'],
            
            // Enrollment
            'classes' => ['array'],
            'classes.*' => ['exists:school_classes,id'],
            'subjects' => ['array'],
            'subjects.*' => ['exists:subjects,id']
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

            // Assign Student role
            $user->assignRole('Student');

            // Create student profile
            $student = Student::create([
                'user_id' => $user->id,
                'student_id' => $request->student_id,
                'admission_date' => $request->admission_date,
                'academic_year_id' => $request->academic_year_id,
                'guardian_name' => $request->guardian_name,
                'guardian_phone' => $request->guardian_phone,
                'guardian_email' => $request->guardian_email,
                'emergency_contact' => $request->emergency_contact,
                'medical_conditions' => $request->medical_conditions,
                'status' => $request->status
            ]);

            // Enroll in classes
            if ($request->filled('classes')) {
                $student->schoolClasses()->sync($request->classes);
            }

            // Enroll in subjects
            if ($request->filled('subjects')) {
                $student->subjects()->sync($request->subjects);
            }
        });

        return redirect()->route('students.index')
            ->with('success', 'Student created successfully.');
    }

    /**
     * Display the specified student.
     */
    public function show(Student $student)
    {
        $student->load([
            'user',
            'academicYear',
            'schoolClasses.subjects',
            'subjects.primaryTeacher.user'
        ]);

        // Academic statistics
        $stats = [
            'total_classes' => $student->schoolClasses->count(),
            'total_subjects' => $student->subjects->count(),
            'enrollment_duration' => $student->admission_date->diffInMonths(now()),
            'current_grade_level' => $student->schoolClasses->first()?->grade_level ?? 'N/A'
        ];

        // Academic performance (mock data)
        $academicPerformance = [
            'overall_gpa' => 3.45,
            'current_grade' => 'B+',
            'attendance_rate' => 94.2,
            'assignments_completed' => 87.5
        ];

        // Recent activities (mock data)
        $recentActivities = [
            [
                'activity' => 'Mathematics Test',
                'score' => '88/100',
                'date' => now()->subDays(2)->format('M j, Y'),
                'type' => 'exam'
            ],
            [
                'activity' => 'Physics Assignment',
                'score' => 'Submitted',
                'date' => now()->subDays(5)->format('M j, Y'),
                'type' => 'assignment'
            ]
        ];

        $breadcrumbs = [
            ['title' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['title' => 'Students', 'url' => route('students.index')],
            ['title' => $student->user->name, 'url' => route('students.show', $student)]
        ];

        return view('admin.students.show', compact('student', 'stats', 'academicPerformance', 'recentActivities', 'breadcrumbs'));
    }

    /**
     * Show the form for editing the specified student.
     */
    public function edit(Student $student)
    {
        $student->load(['user', 'schoolClasses', 'subjects']);
        
        $academicYears = AcademicYear::where('is_active', true)->orderBy('start_date', 'desc')->get();
        $classes = SchoolClass::with('academicYear')->orderBy('grade_level')->orderBy('name')->get();
        $subjects = Subject::orderBy('name')->get();

        $breadcrumbs = [
            ['title' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['title' => 'Students', 'url' => route('students.index')],
            ['title' => $student->user->name, 'url' => route('students.show', $student)],
            ['title' => 'Edit', 'url' => route('students.edit', $student)]
        ];

        return view('admin.students.edit', compact('student', 'academicYears', 'classes', 'subjects', 'breadcrumbs'));
    }

    /**
     * Update the specified student in storage.
     */
    public function update(Request $request, Student $student)
    {
        $request->validate([
            // User fields
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($student->user_id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'phone' => ['nullable', 'string', 'max:20'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'gender' => ['nullable', 'string', 'in:male,female,other'],
            'address' => ['nullable', 'string', 'max:500'],
            
            // Student specific fields
            'student_id' => ['required', 'string', 'max:20', Rule::unique('students')->ignore($student->id)],
            'admission_date' => ['required', 'date'],
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'guardian_name' => ['nullable', 'string', 'max:255'],
            'guardian_phone' => ['nullable', 'string', 'max:20'],
            'guardian_email' => ['nullable', 'email', 'max:255'],
            'emergency_contact' => ['nullable', 'string', 'max:255'],
            'medical_conditions' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', 'string', 'in:active,inactive,graduated,transferred'],
            
            // Enrollment
            'classes' => ['array'],
            'classes.*' => ['exists:school_classes,id'],
            'subjects' => ['array'],
            'subjects.*' => ['exists:subjects,id']
        ]);

        DB::transaction(function () use ($request, $student) {
            // Update user
            $userData = [
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'date_of_birth' => $request->date_of_birth,
                'gender' => $request->gender,
                'address' => $request->address,
            ];

            if ($request->filled('password')) {
                $userData['password'] = Hash::make($request->password);
            }

            $student->user->update($userData);

            // Update student profile
            $student->update([
                'student_id' => $request->student_id,
                'admission_date' => $request->admission_date,
                'academic_year_id' => $request->academic_year_id,
                'guardian_name' => $request->guardian_name,
                'guardian_phone' => $request->guardian_phone,
                'guardian_email' => $request->guardian_email,
                'emergency_contact' => $request->emergency_contact,
                'medical_conditions' => $request->medical_conditions,
                'status' => $request->status
            ]);

            // Update class enrollments
            $student->schoolClasses()->sync($request->input('classes', []));

            // Update subject enrollments
            $student->subjects()->sync($request->input('subjects', []));
        });

        return redirect()->route('students.show', $student)
            ->with('success', 'Student updated successfully.');
    }

    /**
     * Remove the specified student from storage.
     */
    public function destroy(Student $student)
    {
        // Soft delete by deactivating the user instead of hard delete
        DB::transaction(function () use ($student) {
            // Deactivate user
            $student->user->update(['is_active' => false]);
            
            // Update student status
            $student->update(['status' => 'inactive']);
        });

        return redirect()->route('students.index')
            ->with('success', 'Student deactivated successfully.');
    }

    /**
     * Transfer student to different class/academic year.
     */
    public function transfer(Request $request, Student $student)
    {
        $request->validate([
            'new_academic_year_id' => ['required', 'exists:academic_years,id'],
            'new_classes' => ['array'],
            'new_classes.*' => ['exists:school_classes,id'],
            'transfer_reason' => ['nullable', 'string', 'max:500']
        ]);

        DB::transaction(function () use ($request, $student) {
            // Update academic year
            $student->update([
                'academic_year_id' => $request->new_academic_year_id,
                'transfer_reason' => $request->transfer_reason
            ]);

            // Update class enrollments
            $student->schoolClasses()->sync($request->input('new_classes', []));
        });

        return redirect()->route('students.show', $student)
            ->with('success', 'Student transferred successfully.');
    }

    /**
     * Bulk operations on students.
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => ['required', 'string', 'in:activate,deactivate,transfer'],
            'student_ids' => ['required', 'array'],
            'student_ids.*' => ['exists:students,id']
        ]);

        $students = Student::whereIn('id', $request->student_ids);

        switch ($request->action) {
            case 'activate':
                $students->get()->each(function($student) {
                    $student->user->update(['is_active' => true]);
                    $student->update(['status' => 'active']);
                });
                $message = 'Students activated successfully.';
                break;

            case 'deactivate':
                $students->get()->each(function($student) {
                    $student->user->update(['is_active' => false]);
                    $student->update(['status' => 'inactive']);
                });
                $message = 'Students deactivated successfully.';
                break;

            default:
                return redirect()->back()->with('error', 'Invalid action.');
        }

        return redirect()->route('students.index')->with('success', $message);
    }
}