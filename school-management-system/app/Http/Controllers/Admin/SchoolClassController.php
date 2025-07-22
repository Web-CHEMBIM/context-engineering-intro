<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * School Class Management Controller
 * 
 * Handles CRUD operations for school classes with proper relationships
 */
class SchoolClassController extends Controller
{
    /**
     * Display a listing of school classes.
     */
    public function index(Request $request)
    {
        $query = SchoolClass::with(['academicYear', 'classTeacher.user', 'subjects', 'teachers.user'])
            ->withCount(['students' => function($q) {
                $q->whereHas('user', function($query) {
                    $query->where('is_active', true);
                });
            }])
            ->orderBy('grade_level')
            ->orderBy('name');

        // Filter by academic year
        if ($request->filled('academic_year')) {
            $query->where('academic_year_id', $request->academic_year);
        }

        // Filter by grade level
        if ($request->filled('grade')) {
            $query->where('grade_level', $request->grade);
        }

        // Search by name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('section', 'like', "%{$search}%");
            });
        }

        $classes = $query->paginate(20);
        $academicYears = AcademicYear::where('is_active', true)->orderBy('start_date', 'desc')->get();
        $gradeRange = range(1, 12); // Grades 1-12

        $breadcrumbs = [
            ['title' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['title' => 'Classes', 'url' => route('classes.index')]
        ];

        return view('admin.classes.index', compact('classes', 'academicYears', 'gradeRange', 'breadcrumbs'));
    }

    /**
     * Show the form for creating a new school class.
     */
    public function create()
    {
        $academicYears = AcademicYear::where('is_active', true)->orderBy('start_date', 'desc')->get();
        $subjects = Subject::orderBy('name')->get();
        $teachers = Teacher::with('user')->whereHas('user', function($q) {
            $q->where('is_active', true);
        })->get();

        $breadcrumbs = [
            ['title' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['title' => 'Classes', 'url' => route('classes.index')],
            ['title' => 'Create Class', 'url' => route('classes.create')]
        ];

        return view('admin.classes.create', compact('academicYears', 'subjects', 'teachers', 'breadcrumbs'));
    }

    /**
     * Store a newly created school class in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'section' => ['required', 'string', 'max:10'],
            'grade_level' => ['required', 'integer', 'min:1', 'max:12'],
            'capacity' => ['required', 'integer', 'min:1', 'max:100'],
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'class_teacher_id' => ['nullable', 'exists:teachers,id'],
            'room_number' => ['nullable', 'string', 'max:20'],
            'description' => ['nullable', 'string', 'max:500'],
            'subjects' => ['array'],
            'subjects.*' => ['exists:subjects,id'],
            'teachers' => ['array'],
            'teachers.*' => ['exists:teachers,id']
        ], [
            'class_teacher_id.exists' => 'The selected class teacher does not exist.',
            'subjects.*.exists' => 'One or more selected subjects do not exist.',
            'teachers.*.exists' => 'One or more selected teachers do not exist.'
        ]);

        // Check for duplicate class name in the same academic year and grade
        $exists = SchoolClass::where('name', $request->name)
            ->where('section', $request->section)
            ->where('grade_level', $request->grade_level)
            ->where('academic_year_id', $request->academic_year_id)
            ->exists();

        if ($exists) {
            return back()->withErrors([
                'name' => 'A class with this name and section already exists in the selected academic year and grade.'
            ])->withInput();
        }

        DB::transaction(function () use ($request) {
            $class = SchoolClass::create([
                'name' => $request->name,
                'section' => $request->section,
                'grade_level' => $request->grade_level,
                'capacity' => $request->capacity,
                'academic_year_id' => $request->academic_year_id,
                'class_teacher_id' => $request->class_teacher_id,
                'room_number' => $request->room_number,
                'description' => $request->description
            ]);

            // Attach subjects
            if ($request->filled('subjects')) {
                $class->subjects()->sync($request->subjects);
            }

            // Attach teachers
            if ($request->filled('teachers')) {
                $class->teachers()->sync($request->teachers);
            }
        });

        return redirect()->route('classes.index')
            ->with('success', 'Class created successfully.');
    }

    /**
     * Display the specified school class.
     */
    public function show(SchoolClass $class)
    {
        $class->load([
            'academicYear',
            'classTeacher.user',
            'subjects',
            'teachers.user',
            'students.user' => function($query) {
                $query->where('is_active', true);
            }
        ]);

        // Statistics
        $stats = [
            'total_students' => $class->students->count(),
            'capacity_utilization' => $class->capacity > 0 ? round(($class->students->count() / $class->capacity) * 100, 1) : 0,
            'total_subjects' => $class->subjects->count(),
            'total_teachers' => $class->teachers->count()
        ];

        // Student performance data (mock)
        $performanceData = [
            'average_grade' => 78.5,
            'attendance_rate' => 92.3,
            'assignment_completion' => 85.7
        ];

        $breadcrumbs = [
            ['title' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['title' => 'Classes', 'url' => route('classes.index')],
            ['title' => $class->name . ' (' . $class->section . ')', 'url' => route('classes.show', $class)]
        ];

        return view('admin.classes.show', compact('class', 'stats', 'performanceData', 'breadcrumbs'));
    }

    /**
     * Show the form for editing the specified school class.
     */
    public function edit(SchoolClass $class)
    {
        $class->load(['subjects', 'teachers']);
        
        $academicYears = AcademicYear::where('is_active', true)->orderBy('start_date', 'desc')->get();
        $subjects = Subject::orderBy('name')->get();
        $teachers = Teacher::with('user')->whereHas('user', function($q) {
            $q->where('is_active', true);
        })->get();

        $breadcrumbs = [
            ['title' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['title' => 'Classes', 'url' => route('classes.index')],
            ['title' => $class->name . ' (' . $class->section . ')', 'url' => route('classes.show', $class)],
            ['title' => 'Edit', 'url' => route('classes.edit', $class)]
        ];

        return view('admin.classes.edit', compact('class', 'academicYears', 'subjects', 'teachers', 'breadcrumbs'));
    }

    /**
     * Update the specified school class in storage.
     */
    public function update(Request $request, SchoolClass $class)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'section' => ['required', 'string', 'max:10'],
            'grade_level' => ['required', 'integer', 'min:1', 'max:12'],
            'capacity' => ['required', 'integer', 'min:1', 'max:100'],
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'class_teacher_id' => ['nullable', 'exists:teachers,id'],
            'room_number' => ['nullable', 'string', 'max:20'],
            'description' => ['nullable', 'string', 'max:500'],
            'subjects' => ['array'],
            'subjects.*' => ['exists:subjects,id'],
            'teachers' => ['array'],
            'teachers.*' => ['exists:teachers,id']
        ]);

        // Check for duplicate class name (excluding current class)
        $exists = SchoolClass::where('name', $request->name)
            ->where('section', $request->section)
            ->where('grade_level', $request->grade_level)
            ->where('academic_year_id', $request->academic_year_id)
            ->where('id', '!=', $class->id)
            ->exists();

        if ($exists) {
            return back()->withErrors([
                'name' => 'A class with this name and section already exists in the selected academic year and grade.'
            ])->withInput();
        }

        DB::transaction(function () use ($request, $class) {
            $class->update([
                'name' => $request->name,
                'section' => $request->section,
                'grade_level' => $request->grade_level,
                'capacity' => $request->capacity,
                'academic_year_id' => $request->academic_year_id,
                'class_teacher_id' => $request->class_teacher_id,
                'room_number' => $request->room_number,
                'description' => $request->description
            ]);

            // Sync subjects
            $class->subjects()->sync($request->input('subjects', []));

            // Sync teachers
            $class->teachers()->sync($request->input('teachers', []));
        });

        return redirect()->route('classes.show', $class)
            ->with('success', 'Class updated successfully.');
    }

    /**
     * Remove the specified school class from storage.
     */
    public function destroy(SchoolClass $class)
    {
        // Check if class has students
        if ($class->students()->count() > 0) {
            return redirect()->route('classes.index')
                ->with('error', 'Cannot delete class with enrolled students.');
        }

        DB::transaction(function () use ($class) {
            // Detach relationships
            $class->subjects()->detach();
            $class->teachers()->detach();
            
            // Delete the class
            $class->delete();
        });

        return redirect()->route('classes.index')
            ->with('success', 'Class deleted successfully.');
    }

    /**
     * Manage student enrollments for a class.
     */
    public function manageStudents(SchoolClass $class)
    {
        $class->load(['students.user', 'academicYear']);
        
        // Available students (not enrolled in this class)
        $availableStudents = \App\Models\Student::with('user')
            ->whereHas('user', function($q) {
                $q->where('is_active', true);
            })
            ->whereDoesntHave('schoolClasses', function($q) use ($class) {
                $q->where('school_classes.id', $class->id);
            })
            ->where('academic_year_id', $class->academic_year_id)
            ->get();

        $breadcrumbs = [
            ['title' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['title' => 'Classes', 'url' => route('classes.index')],
            ['title' => $class->name . ' (' . $class->section . ')', 'url' => route('classes.show', $class)],
            ['title' => 'Manage Students', 'url' => '#']
        ];

        return view('admin.classes.manage-students', compact('class', 'availableStudents', 'breadcrumbs'));
    }
}