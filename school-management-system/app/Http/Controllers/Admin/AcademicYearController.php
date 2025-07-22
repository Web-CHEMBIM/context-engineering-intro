<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Academic Year Management Controller
 * 
 * Handles CRUD operations for academic years
 */
class AcademicYearController extends Controller
{
    /**
     * Display a listing of academic years.
     */
    public function index(Request $request)
    {
        $query = AcademicYear::withCount(['students', 'schoolClasses'])
            ->orderBy('start_date', 'desc');

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'current') {
                $query->where('is_current', true);
            } elseif ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $academicYears = $query->paginate(15);

        $breadcrumbs = [
            ['title' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['title' => 'Academic Years', 'url' => route('academic-years.index')]
        ];

        return view('admin.academic-years.index', compact('academicYears', 'breadcrumbs'));
    }

    /**
     * Show the form for creating a new academic year.
     */
    public function create()
    {
        $breadcrumbs = [
            ['title' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['title' => 'Academic Years', 'url' => route('academic-years.index')],
            ['title' => 'Create Academic Year', 'url' => route('academic-years.create')]
        ];

        return view('admin.academic-years.create', compact('breadcrumbs'));
    }

    /**
     * Store a newly created academic year in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:academic_years,name'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'description' => ['nullable', 'string', 'max:500'],
            'is_current' => ['boolean'],
            'is_active' => ['boolean']
        ]);

        DB::transaction(function () use ($request) {
            // If setting as current, remove current status from other years
            if ($request->boolean('is_current')) {
                AcademicYear::where('is_current', true)->update(['is_current' => false]);
            }

            AcademicYear::create([
                'name' => $request->name,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'description' => $request->description,
                'is_current' => $request->boolean('is_current'),
                'is_active' => $request->boolean('is_active', true)
            ]);
        });

        return redirect()->route('academic-years.index')
            ->with('success', 'Academic year created successfully.');
    }

    /**
     * Display the specified academic year.
     */
    public function show(AcademicYear $academicYear)
    {
        $academicYear->load(['students.user', 'schoolClasses']);

        // Statistics
        $stats = [
            'total_students' => $academicYear->students()->whereHas('user', function($q) {
                $q->where('is_active', true);
            })->count(),
            'total_classes' => $academicYear->schoolClasses()->count(),
            'enrollment_trend' => $this->getEnrollmentTrend($academicYear),
            'completion_rate' => $this->getCompletionRate($academicYear)
        ];

        $breadcrumbs = [
            ['title' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['title' => 'Academic Years', 'url' => route('academic-years.index')],
            ['title' => $academicYear->name, 'url' => route('academic-years.show', $academicYear)]
        ];

        return view('admin.academic-years.show', compact('academicYear', 'stats', 'breadcrumbs'));
    }

    /**
     * Show the form for editing the specified academic year.
     */
    public function edit(AcademicYear $academicYear)
    {
        $breadcrumbs = [
            ['title' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['title' => 'Academic Years', 'url' => route('academic-years.index')],
            ['title' => $academicYear->name, 'url' => route('academic-years.show', $academicYear)],
            ['title' => 'Edit', 'url' => route('academic-years.edit', $academicYear)]
        ];

        return view('admin.academic-years.edit', compact('academicYear', 'breadcrumbs'));
    }

    /**
     * Update the specified academic year in storage.
     */
    public function update(Request $request, AcademicYear $academicYear)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:academic_years,name,' . $academicYear->id],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'description' => ['nullable', 'string', 'max:500'],
            'is_current' => ['boolean'],
            'is_active' => ['boolean']
        ]);

        DB::transaction(function () use ($request, $academicYear) {
            // If setting as current, remove current status from other years
            if ($request->boolean('is_current') && !$academicYear->is_current) {
                AcademicYear::where('is_current', true)->update(['is_current' => false]);
            }

            $academicYear->update([
                'name' => $request->name,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'description' => $request->description,
                'is_current' => $request->boolean('is_current'),
                'is_active' => $request->boolean('is_active', true)
            ]);
        });

        return redirect()->route('academic-years.show', $academicYear)
            ->with('success', 'Academic year updated successfully.');
    }

    /**
     * Remove the specified academic year from storage.
     */
    public function destroy(AcademicYear $academicYear)
    {
        // Check if academic year has associated data
        if ($academicYear->students()->count() > 0 || $academicYear->schoolClasses()->count() > 0) {
            return redirect()->route('academic-years.index')
                ->with('error', 'Cannot delete academic year with associated students or classes.');
        }

        // Prevent deletion of current academic year
        if ($academicYear->is_current) {
            return redirect()->route('academic-years.index')
                ->with('error', 'Cannot delete the current academic year.');
        }

        $academicYear->delete();

        return redirect()->route('academic-years.index')
            ->with('success', 'Academic year deleted successfully.');
    }

    /**
     * Set the specified academic year as current.
     */
    public function setCurrent(AcademicYear $academicYear)
    {
        if (!$academicYear->is_active) {
            return redirect()->route('academic-years.index')
                ->with('error', 'Cannot set inactive academic year as current.');
        }

        DB::transaction(function () use ($academicYear) {
            // Remove current status from all years
            AcademicYear::where('is_current', true)->update(['is_current' => false]);
            
            // Set this year as current
            $academicYear->update(['is_current' => true]);
        });

        return redirect()->route('academic-years.index')
            ->with('success', 'Academic year set as current successfully.');
    }

    /**
     * Get enrollment trend data for the academic year.
     */
    private function getEnrollmentTrend(AcademicYear $academicYear)
    {
        // Mock data - in real implementation, this would calculate monthly enrollment
        return collect([
            ['month' => 'Sep', 'count' => 150],
            ['month' => 'Oct', 'count' => 175],
            ['month' => 'Nov', 'count' => 180],
            ['month' => 'Dec', 'count' => 185],
            ['month' => 'Jan', 'count' => 190],
            ['month' => 'Feb', 'count' => 195]
        ]);
    }

    /**
     * Get completion rate for the academic year.
     */
    private function getCompletionRate(AcademicYear $academicYear)
    {
        // Mock data - in real implementation, this would calculate actual completion rate
        return 85.5;
    }
}