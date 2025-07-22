<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Student;
use App\Models\SchoolClass;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Student Dashboard Controller
 * 
 * Handles student-specific dashboard and functionality
 */
class StudentDashboardController extends Controller
{
    /**
     * Display the student dashboard.
     */
    public function index()
    {
        $user = Auth::user();
        $student = $user->student;

        if (!$student) {
            return redirect()->route('login')->with('error', 'Student profile not found.');
        }

        // Student's classes
        $myClasses = $student->schoolClasses()->with(['teachers.user', 'subjects'])->get();

        // Student's subjects
        $mySubjects = $student->subjects()->with(['teachers.user'])->get();

        // Statistics
        $stats = [
            'total_classes' => $myClasses->count(),
            'total_subjects' => $mySubjects->count(),
            'current_grade' => $myClasses->first()?->grade_level ?? 'N/A',
            'enrollment_year' => $student->created_at->format('Y')
        ];

        // My Classes Data
        $classesData = $myClasses->map(function($class) {
            return [
                'title' => $class->name . ' (' . $class->section . ')',
                'subtitle' => 'Grade ' . $class->grade_level . ' • ' . $class->teachers->count() . ' teachers',
                'badge' => $class->subjects->count(),
                'badgeColor' => 'primary',
                'icon' => 'book-open',
                'iconColor' => 'primary'
            ];
        });

        // My Subjects Data
        $subjectsData = $mySubjects->map(function($subject) {
            $primaryTeacher = $subject->teachers->first();
            return [
                'title' => $subject->name . ' (' . $subject->code . ')',
                'subtitle' => $primaryTeacher ? 
                    'Teacher: ' . $primaryTeacher->user->name : 
                    'No teacher assigned',
                'badge' => $subject->credit_hours . 'h',
                'badgeColor' => 'success',
                'icon' => 'book',
                'iconColor' => 'success'
            ];
        });

        // Quick Actions for Students
        $quickActions = [
            [
                'title' => 'My Grades',
                'icon' => 'award',
                'color' => 'success',
                'href' => route('student.grades'),
                'count' => 0 // Will be implemented later
            ],
            [
                'title' => 'My Subjects',
                'icon' => 'book',
                'color' => 'primary',
                'href' => route('student.subjects'),
                'count' => $stats['total_subjects']
            ],
            [
                'title' => 'Assignments',
                'icon' => 'file-text',
                'color' => 'warning',
                'href' => route('student.assignments'),
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

        // Academic Progress (mock data for now)
        $academicProgress = [
            'overall_grade' => 85,
            'attendance_rate' => 94,
            'assignments_completed' => 78,
            'exam_average' => 82
        ];

        // Recent Activities (mock data)
        $recentActivities = [
            [
                'title' => 'Mathematics Test Completed',
                'subtitle' => 'Score: 88/100',
                'time' => '2 days ago',
                'icon' => 'check-circle',
                'iconColor' => 'success'
            ],
            [
                'title' => 'Physics Assignment Submitted',
                'subtitle' => 'Lab Report #3',
                'time' => '3 days ago',
                'icon' => 'upload',
                'iconColor' => 'primary'
            ],
            [
                'title' => 'English Essay Graded',
                'subtitle' => 'Score: 92/100',
                'time' => '1 week ago',
                'icon' => 'award',
                'iconColor' => 'warning'
            ]
        ];

        // Today's Schedule (mock data)
        $todaySchedule = [
            [
                'title' => 'Mathematics',
                'subtitle' => '9:00 AM - 10:00 AM • Room 101',
                'icon' => 'clock',
                'iconColor' => 'primary'
            ],
            [
                'title' => 'Physics',
                'subtitle' => '11:00 AM - 12:00 PM • Lab A',
                'icon' => 'clock',
                'iconColor' => 'success'
            ],
            [
                'title' => 'English Literature',
                'subtitle' => '2:00 PM - 3:00 PM • Room 205',
                'icon' => 'clock',
                'iconColor' => 'warning'
            ]
        ];

        $breadcrumbs = [
            ['title' => 'Student Portal', 'url' => route('student.dashboard')]
        ];

        return view('student.dashboard', compact(
            'stats',
            'classesData',
            'subjectsData',
            'quickActions',
            'academicProgress',
            'recentActivities',
            'todaySchedule',
            'student',
            'breadcrumbs'
        ));
    }

    /**
     * Display student's grades.
     */
    public function grades()
    {
        $user = Auth::user();
        $student = $user->student;

        // Mock grades data for now - will be implemented in future tasks
        $grades = collect([
            [
                'subject' => 'Mathematics',
                'teacher' => 'Mr. Johnson',
                'grade' => 'A-',
                'percentage' => 88,
                'last_updated' => now()->subDays(3)->format('M j, Y')
            ],
            [
                'subject' => 'Physics',
                'teacher' => 'Dr. Smith',
                'grade' => 'B+',
                'percentage' => 85,
                'last_updated' => now()->subDays(5)->format('M j, Y')
            ],
            [
                'subject' => 'English Literature',
                'teacher' => 'Ms. Brown',
                'grade' => 'A',
                'percentage' => 92,
                'last_updated' => now()->subWeek()->format('M j, Y')
            ]
        ]);

        $breadcrumbs = [
            ['title' => 'Student Portal', 'url' => route('student.dashboard')],
            ['title' => 'My Grades', 'url' => route('student.grades')]
        ];

        return view('student.grades', compact('grades', 'student', 'breadcrumbs'));
    }

    /**
     * Display student's subjects.
     */
    public function subjects()
    {
        $user = Auth::user();
        $student = $user->student;

        $subjects = $student->subjects()->with(['teachers.user'])->get();

        $breadcrumbs = [
            ['title' => 'Student Portal', 'url' => route('student.dashboard')],
            ['title' => 'My Subjects', 'url' => route('student.subjects')]
        ];

        return view('student.subjects', compact('subjects', 'student', 'breadcrumbs'));
    }

    /**
     * Display student's assignments.
     */
    public function assignments()
    {
        $user = Auth::user();
        $student = $user->student;

        // Mock assignments data for now - will be implemented in future tasks
        $assignments = collect([
            [
                'title' => 'Mathematics - Algebra Test',
                'subject' => 'Mathematics',
                'teacher' => 'Mr. Johnson',
                'due_date' => now()->addDays(5)->format('M j, Y'),
                'status' => 'pending',
                'description' => 'Chapter 5-7 algebra problems'
            ],
            [
                'title' => 'Physics - Lab Report #4',
                'subject' => 'Physics',
                'teacher' => 'Dr. Smith',
                'due_date' => now()->addDays(10)->format('M j, Y'),
                'status' => 'in_progress',
                'description' => 'Motion and forces experiment report'
            ]
        ]);

        $breadcrumbs = [
            ['title' => 'Student Portal', 'url' => route('student.dashboard')],
            ['title' => 'My Assignments', 'url' => route('student.assignments')]
        ];

        return view('student.assignments', compact('assignments', 'student', 'breadcrumbs'));
    }
}