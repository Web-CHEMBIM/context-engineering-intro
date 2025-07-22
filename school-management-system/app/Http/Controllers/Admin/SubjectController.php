<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Subject Management Controller
 * 
 * Handles CRUD operations for subjects with teacher assignments
 */
class SubjectController extends Controller
{
    /**
     * Display a listing of subjects.
     */
    public function index(Request $request)
    {
        $query = Subject::with(['primaryTeacher.user'])
            ->withCount(['teachers', 'students', 'schoolClasses'])
            ->orderBy('name');

        // Filter by department
        if ($request->filled('department')) {
            $query->where('department', $request->department);
        }

        // Filter by subject type
        if ($request->filled('type')) {
            if ($request->type === 'core') {
                $query->where('is_core', true);
            } elseif ($request->type === 'elective') {
                $query->where('is_core', false);
            }
        }

        // Search by name or code
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $subjects = $query->paginate(20);
        
        // Get unique departments for filter
        $departments = Subject::distinct()->pluck('department')->filter()->sort();

        $breadcrumbs = [
            ['title' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['title' => 'Subjects', 'url' => route('subjects.index')]
        ];

        return view('admin.subjects.index', compact('subjects', 'departments', 'breadcrumbs'));
    }

    /**
     * Show the form for creating a new subject.
     */
    public function create()
    {
        $teachers = Teacher::with('user')->whereHas('user', function($q) {
            $q->where('is_active', true);
        })->get();

        $breadcrumbs = [
            ['title' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['title' => 'Subjects', 'url' => route('subjects.index')],
            ['title' => 'Create Subject', 'url' => route('subjects.create')]
        ];

        return view('admin.subjects.create', compact('teachers', 'breadcrumbs'));
    }

    /**
     * Store a newly created subject in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:subjects,name'],
            'code' => ['required', 'string', 'max:20', 'unique:subjects,code'],
            'description' => ['nullable', 'string', 'max:1000'],
            'credit_hours' => ['required', 'integer', 'min:1', 'max:10'],
            'department' => ['nullable', 'string', 'max:100'],
            'is_core' => ['boolean'],
            'primary_teacher_id' => ['nullable', 'exists:teachers,id'],
            'prerequisites' => ['nullable', 'string', 'max:500'],
            'grade_levels' => ['array'],
            'grade_levels.*' => ['integer', 'min:1', 'max:12']
        ]);

        $subject = Subject::create([
            'name' => $request->name,
            'code' => $request->code,
            'description' => $request->description,
            'credit_hours' => $request->credit_hours,
            'department' => $request->department,
            'is_core' => $request->boolean('is_core'),
            'primary_teacher_id' => $request->primary_teacher_id,
            'prerequisites' => $request->prerequisites,
            'grade_levels' => $request->input('grade_levels', [])
        ]);

        // If primary teacher is assigned, also add them as a regular teacher
        if ($request->primary_teacher_id) {
            $subject->teachers()->attach($request->primary_teacher_id);
        }

        return redirect()->route('subjects.index')
            ->with('success', 'Subject created successfully.');
    }

    /**
     * Display the specified subject.
     */
    public function show(Subject $subject)
    {
        $subject->load([
            'primaryTeacher.user',
            'teachers.user',
            'students.user' => function($query) {
                $query->where('is_active', true);
            },
            'schoolClasses'
        ]);

        // Statistics
        $stats = [
            'total_teachers' => $subject->teachers->count(),
            'total_students' => $subject->students->count(),
            'total_classes' => $subject->schoolClasses->count(),
            'average_class_size' => $subject->schoolClasses->count() > 0 ? 
                round($subject->students->count() / $subject->schoolClasses->count(), 1) : 0
        ];

        // Grade distribution (mock data)
        $gradeDistribution = [
            'A' => 15,
            'B' => 25,
            'C' => 30,
            'D' => 20,
            'F' => 10
        ];

        // Performance metrics (mock data)
        $performanceMetrics = [
            'average_score' => 76.5,
            'pass_rate' => 85.2,
            'attendance_rate' => 89.7
        ];

        $breadcrumbs = [
            ['title' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['title' => 'Subjects', 'url' => route('subjects.index')],
            ['title' => $subject->name, 'url' => route('subjects.show', $subject)]
        ];

        return view('admin.subjects.show', compact('subject', 'stats', 'gradeDistribution', 'performanceMetrics', 'breadcrumbs'));
    }

    /**
     * Show the form for editing the specified subject.
     */
    public function edit(Subject $subject)
    {
        $subject->load(['teachers']);
        
        $teachers = Teacher::with('user')->whereHas('user', function($q) {
            $q->where('is_active', true);
        })->get();

        $breadcrumbs = [
            ['title' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['title' => 'Subjects', 'url' => route('subjects.index')],
            ['title' => $subject->name, 'url' => route('subjects.show', $subject)],
            ['title' => 'Edit', 'url' => route('subjects.edit', $subject)]
        ];

        return view('admin.subjects.edit', compact('subject', 'teachers', 'breadcrumbs'));
    }

    /**
     * Update the specified subject in storage.
     */
    public function update(Request $request, Subject $subject)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:subjects,name,' . $subject->id],
            'code' => ['required', 'string', 'max:20', 'unique:subjects,code,' . $subject->id],
            'description' => ['nullable', 'string', 'max:1000'],
            'credit_hours' => ['required', 'integer', 'min:1', 'max:10'],
            'department' => ['nullable', 'string', 'max:100'],
            'is_core' => ['boolean'],
            'primary_teacher_id' => ['nullable', 'exists:teachers,id'],
            'prerequisites' => ['nullable', 'string', 'max:500'],
            'grade_levels' => ['array'],
            'grade_levels.*' => ['integer', 'min:1', 'max:12']
        ]);

        DB::transaction(function () use ($request, $subject) {
            // Store old primary teacher
            $oldPrimaryTeacher = $subject->primary_teacher_id;
            
            // Update subject
            $subject->update([
                'name' => $request->name,
                'code' => $request->code,
                'description' => $request->description,
                'credit_hours' => $request->credit_hours,
                'department' => $request->department,
                'is_core' => $request->boolean('is_core'),
                'primary_teacher_id' => $request->primary_teacher_id,
                'prerequisites' => $request->prerequisites,
                'grade_levels' => $request->input('grade_levels', [])
            ]);

            // Handle primary teacher changes
            if ($request->primary_teacher_id && $request->primary_teacher_id != $oldPrimaryTeacher) {
                // Remove old primary teacher from teachers list if they exist
                if ($oldPrimaryTeacher) {
                    $subject->teachers()->detach($oldPrimaryTeacher);
                }
                
                // Add new primary teacher to teachers list
                $subject->teachers()->syncWithoutDetaching([$request->primary_teacher_id]);
            } elseif (!$request->primary_teacher_id && $oldPrimaryTeacher) {
                // Primary teacher removed, but keep them in teachers list
                // Admin can manually remove from teachers list if needed
            }
        });

        return redirect()->route('subjects.show', $subject)
            ->with('success', 'Subject updated successfully.');
    }

    /**
     * Remove the specified subject from storage.
     */
    public function destroy(Subject $subject)
    {
        // Check if subject has students or is assigned to classes
        if ($subject->students()->count() > 0 || $subject->schoolClasses()->count() > 0) {
            return redirect()->route('subjects.index')
                ->with('error', 'Cannot delete subject with enrolled students or assigned classes.');
        }

        DB::transaction(function () use ($subject) {
            // Detach all teachers
            $subject->teachers()->detach();
            
            // Delete the subject
            $subject->delete();
        });

        return redirect()->route('subjects.index')
            ->with('success', 'Subject deleted successfully.');
    }

    /**
     * Assign teachers to the subject.
     */
    public function assignTeachers(Request $request, Subject $subject)
    {
        $request->validate([
            'teachers' => ['required', 'array'],
            'teachers.*' => ['exists:teachers,id']
        ]);

        $subject->teachers()->sync($request->teachers);

        return redirect()->route('subjects.show', $subject)
            ->with('success', 'Teachers assigned successfully.');
    }

    /**
     * Remove teacher from the subject.
     */
    public function removeTeacher(Subject $subject, Teacher $teacher)
    {
        // Prevent removing primary teacher
        if ($subject->primary_teacher_id == $teacher->id) {
            return redirect()->route('subjects.show', $subject)
                ->with('error', 'Cannot remove primary teacher. Please assign a different primary teacher first.');
        }

        $subject->teachers()->detach($teacher->id);

        return redirect()->route('subjects.show', $subject)
            ->with('success', 'Teacher removed from subject successfully.');
    }

    /**
     * Get subjects by department (AJAX endpoint).
     */
    public function getByDepartment(Request $request)
    {
        $department = $request->get('department');
        
        $subjects = Subject::where('department', $department)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        return response()->json($subjects);
    }
}