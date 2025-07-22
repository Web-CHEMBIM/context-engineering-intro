<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\StudentResource;
use App\Models\Student;
use App\Models\User;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class StudentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Student::class);

        $query = Student::with(['user', 'academicYear', 'classes', 'subjects']);

        // Apply filters
        if ($request->filled('academic_year_id')) {
            $query->where('academic_year_id', $request->academic_year_id);
        }

        if ($request->filled('grade_level')) {
            $query->whereHas('classes', function($q) use ($request) {
                $q->where('grade_level', $request->grade_level);
            });
        }

        if ($request->filled('is_active')) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('is_active', $request->boolean('is_active'));
            });
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('student_id', 'like', "%{$search}%")
                  ->orWhereHas('user', function($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%")
                               ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        // Apply sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');
        
        if ($sortBy === 'name') {
            $query->join('users', 'students.user_id', '=', 'users.id')
                  ->orderBy('users.name', $sortDirection)
                  ->select('students.*');
        } else {
            $query->orderBy($sortBy, $sortDirection);
        }

        // Include counts
        $query->withCount(['classes', 'subjects']);

        // Paginate results
        $perPage = min($request->get('per_page', 15), 100);
        $students = $query->paginate($perPage);

        return StudentResource::collection($students)->additional([
            'meta' => [
                'total_count' => $students->total(),
                'per_page' => $students->perPage(),
                'current_page' => $students->currentPage(),
                'last_page' => $students->lastPage(),
                'filters_applied' => $request->only(['academic_year_id', 'grade_level', 'is_active', 'search']),
            ]
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Student::class);

        $validator = Validator::make($request->all(), [
            // User data
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'phone' => 'nullable|string|max:20',
            'date_of_birth' => 'required|date|before:today',
            'gender' => 'required|in:male,female,other',
            
            // Student data
            'student_id' => 'nullable|string|max:50|unique:students',
            'academic_year_id' => 'required|exists:academic_years,id',
            'admission_date' => 'required|date',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'medical_conditions' => 'nullable|string',
            'total_fees' => 'required|numeric|min:0',
            
            // Enrollments
            'classes' => 'array',
            'classes.*' => 'exists:school_classes,id',
            'subjects' => 'array',
            'subjects.*' => 'exists:subjects,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        DB::beginTransaction();
        try {
            // Create user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'phone' => $request->phone,
                'date_of_birth' => $request->date_of_birth,
                'gender' => $request->gender,
                'is_active' => true,
            ]);

            // Assign student role
            $user->assignRole('Student');

            // Generate student ID if not provided
            $studentId = $request->student_id ?: $this->generateStudentId();

            // Create student
            $student = Student::create([
                'user_id' => $user->id,
                'student_id' => $studentId,
                'academic_year_id' => $request->academic_year_id,
                'admission_date' => $request->admission_date,
                'emergency_contact_name' => $request->emergency_contact_name,
                'emergency_contact_phone' => $request->emergency_contact_phone,
                'medical_conditions' => $request->medical_conditions,
                'total_fees' => $request->total_fees,
                'fees_paid' => 0,
                'fees_pending' => $request->total_fees,
            ]);

            // Enroll in classes if provided
            if ($request->has('classes')) {
                $student->classes()->sync($request->classes);
            }

            // Enroll in subjects if provided
            if ($request->has('subjects')) {
                $student->subjects()->sync($request->subjects);
            }

            DB::commit();

            $student->load(['user', 'academicYear', 'classes', 'subjects']);
            return new StudentResource($student);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'message' => 'Failed to create student',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Student $student)
    {
        $this->authorize('view', $student);

        $student->load(['user', 'academicYear', 'classes', 'subjects']);

        return new StudentResource($student);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Student $student)
    {
        $this->authorize('update', $student);

        $validator = Validator::make($request->all(), [
            // User data
            'name' => 'string|max:255',
            'email' => 'string|email|max:255|unique:users,email,' . $student->user_id,
            'phone' => 'nullable|string|max:20',
            'date_of_birth' => 'date|before:today',
            'gender' => 'in:male,female,other',
            
            // Student data
            'student_id' => 'string|max:50|unique:students,student_id,' . $student->id,
            'academic_year_id' => 'exists:academic_years,id',
            'admission_date' => 'date',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'medical_conditions' => 'nullable|string',
            'fees_paid' => 'numeric|min:0',
            'fees_pending' => 'numeric|min:0',
            'total_fees' => 'numeric|min:0',
            
            // Enrollments
            'classes' => 'array',
            'classes.*' => 'exists:school_classes,id',
            'subjects' => 'array',
            'subjects.*' => 'exists:subjects,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        DB::beginTransaction();
        try {
            // Update user data
            $userUpdateData = $request->only(['name', 'email', 'phone', 'date_of_birth', 'gender']);
            if (!empty($userUpdateData)) {
                $student->user->update($userUpdateData);
            }

            // Update student data
            $studentUpdateData = $request->only([
                'student_id', 'academic_year_id', 'admission_date',
                'emergency_contact_name', 'emergency_contact_phone', 
                'medical_conditions', 'fees_paid', 'fees_pending', 'total_fees'
            ]);
            if (!empty($studentUpdateData)) {
                $student->update($studentUpdateData);
            }

            // Update class enrollments
            if ($request->has('classes')) {
                $student->classes()->sync($request->classes);
            }

            // Update subject enrollments
            if ($request->has('subjects')) {
                $student->subjects()->sync($request->subjects);
            }

            DB::commit();

            $student->load(['user', 'academicYear', 'classes', 'subjects']);
            return new StudentResource($student);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'message' => 'Failed to update student',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Student $student)
    {
        $this->authorize('delete', $student);

        // Soft delete by deactivating user account
        $student->user->update(['is_active' => false]);

        return response()->json([
            'message' => 'Student deactivated successfully'
        ], Response::HTTP_OK);
    }

    /**
     * Get student's academic performance.
     */
    public function performance(Student $student)
    {
        $this->authorize('view', $student);

        // This would typically fetch grades, attendance, etc.
        // For now, return mock data
        return response()->json([
            'student_id' => $student->student_id,
            'academic_year' => $student->academicYear->name,
            'overall_grade' => rand(70, 95),
            'attendance_rate' => rand(85, 98),
            'subjects_enrolled' => $student->subjects->count(),
            'classes_enrolled' => $student->classes->count(),
        ]);
    }

    /**
     * Generate a unique student ID.
     */
    private function generateStudentId(): string
    {
        $currentYear = now()->year;
        $lastStudent = Student::where('student_id', 'like', $currentYear . '%')
                             ->orderBy('student_id', 'desc')
                             ->first();

        if ($lastStudent) {
            $lastNumber = (int) substr($lastStudent->student_id, -4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return $currentYear . $newNumber;
    }
}