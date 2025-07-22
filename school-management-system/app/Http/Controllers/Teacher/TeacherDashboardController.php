<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Teacher;
use App\Models\Student;
use App\Models\SchoolClass;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Teacher Dashboard Controller
 * 
 * Handles teacher-specific dashboard and functionality
 */
class TeacherDashboardController extends Controller
{
    /**
     * Display the teacher dashboard.
     */
    public function index()
    {
        $user = Auth::user();
        $teacher = $user->teacher;

        if (!$teacher) {
            return redirect()->route('login')->with('error', 'Teacher profile not found.');
        }

        // Teacher's assigned classes
        $myClasses = $teacher->schoolClasses()
            ->with(['students' => function($query) {
                $query->whereHas('user', function($q) {
                    $q->where('is_active', true);
                });
            }])
            ->get();

        // Teacher's subjects
        $mySubjects = $teacher->subjects()->get();

        // Statistics
        $stats = [
            'total_classes' => $myClasses->count(),
            'total_subjects' => $mySubjects->count(),
            'total_students' => $myClasses->sum(function($class) {
                return $class->students->count();
            }),
            'average_class_size' => $myClasses->count() > 0 ? 
                round($myClasses->sum(function($class) {
                    return $class->students->count();
                }) / $myClasses->count()) : 0
        ];

        // My Classes Data
        $classesData = $myClasses->map(function($class) {
            return [
                'title' => $class->name . ' (' . $class->section . ')',
                'subtitle' => 'Grade ' . $class->grade_level . ' • ' . $class->students->count() . ' students',
                'badge' => $class->students->count(),
                'badgeColor' => 'primary',
                'icon' => 'users',
                'iconColor' => 'primary'
            ];
        });

        // Recent Students (from teacher's classes)
        $recentStudents = Student::whereHas('schoolClasses', function($query) use ($teacher) {
            $query->whereIn('school_classes.id', $teacher->schoolClasses->pluck('id'));
        })
        ->with('user')
        ->whereHas('user', function($query) {
            $query->where('is_active', true);
        })
        ->latest('created_at')
        ->take(8)
        ->get()
        ->map(function($student) {
            return [
                'title' => $student->user->name,
                'subtitle' => 'Student ID: ' . $student->student_id,
                'time' => $student->created_at->diffForHumans(),
                'avatar' => $student->user->profile_photo_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($student->user->name) . '&background=0ea5e9&color=ffffff'
            ];
        });

        // Subject Statistics
        $subjectStats = $mySubjects->map(function($subject) use ($teacher) {
            $studentsCount = $subject->students()
                ->whereHas('schoolClasses', function($query) use ($teacher) {
                    $query->whereIn('school_classes.id', $teacher->schoolClasses->pluck('id'));
                })
                ->count();
                
            return [
                'name' => $subject->name,
                'code' => $subject->code,
                'students' => $studentsCount,
                'credit_hours' => $subject->credit_hours
            ];
        });

        // Quick Actions for Teachers
        $quickActions = [
            [
                'title' => 'My Classes',
                'icon' => 'grid-3x3',
                'color' => 'primary',
                'href' => route('teacher.classes'),
                'count' => $stats['total_classes']
            ],
            [
                'title' => 'My Students',
                'icon' => 'users',
                'color' => 'success',
                'href' => route('teacher.students'),
                'count' => $stats['total_students']
            ],
            [
                'title' => 'Assignments',
                'icon' => 'file-text',
                'color' => 'warning',
                'href' => route('teacher.assignments'),
                'count' => 0 // Will be implemented later
            ],
            [
                'title' => 'Attendance',
                'icon' => 'check-circle',
                'color' => 'info',
                'href' => '#',
                'count' => null
            ]
        ];

        // Today's Schedule (mock data for now)
        $todaySchedule = [
            [
                'title' => 'Mathematics - Grade 10A',
                'subtitle' => '9:00 AM - 10:00 AM',
                'icon' => 'clock',
                'iconColor' => 'primary'
            ],
            [
                'title' => 'Physics - Grade 11B',
                'subtitle' => '11:00 AM - 12:00 PM',
                'icon' => 'clock',
                'iconColor' => 'success'
            ],
            [
                'title' => 'Mathematics - Grade 9C',
                'subtitle' => '2:00 PM - 3:00 PM',
                'icon' => 'clock',
                'iconColor' => 'warning'
            ]
        ];

        $breadcrumbs = [
            ['title' => 'Teacher Portal', 'url' => route('teacher.dashboard')]
        ];

        return view('teacher.dashboard', compact(
            'stats',
            'classesData',
            'recentStudents',
            'subjectStats',
            'quickActions',
            'todaySchedule',
            'teacher',
            'breadcrumbs'
        ));
    }

    /**
     * Display teacher's classes.
     */
    public function classes()
    {
        $user = Auth::user();
        $teacher = $user->teacher;

        $classes = $teacher->schoolClasses()
            ->with(['students.user', 'subjects'])
            ->get();

        $breadcrumbs = [
            ['title' => 'Teacher Portal', 'url' => route('teacher.dashboard')],
            ['title' => 'My Classes', 'url' => route('teacher.classes')]
        ];

        return view('teacher.classes', compact('classes', 'teacher', 'breadcrumbs'));
    }

    /**
     * Display teacher's students.
     */
    public function students()
    {
        $user = Auth::user();
        $teacher = $user->teacher;

        $students = Student::whereHas('schoolClasses', function($query) use ($teacher) {
            $query->whereIn('school_classes.id', $teacher->schoolClasses->pluck('id'));
        })
        ->with(['user', 'schoolClasses'])
        ->whereHas('user', function($query) {
            $query->where('is_active', true);
        })
        ->get();

        $breadcrumbs = [
            ['title' => 'Teacher Portal', 'url' => route('teacher.dashboard')],
            ['title' => 'My Students', 'url' => route('teacher.students')]
        ];

        return view('teacher.students', compact('students', 'teacher', 'breadcrumbs'));
    }

    /**
     * Display teacher's assignments.
     */
    public function assignments()
    {
        $user = Auth::user();
        $teacher = $user->teacher;

        // Mock data for now - will be implemented in future tasks
        $assignments = collect();

        $breadcrumbs = [
            ['title' => 'Teacher Portal', 'url' => route('teacher.dashboard')],
            ['title' => 'Assignments', 'url' => route('teacher.assignments')]
        ];

        return view('teacher.assignments', compact('assignments', 'teacher', 'breadcrumbs'));
    }
}