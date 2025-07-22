<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\TeacherResource;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class TeacherController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Teacher::class);

        $query = Teacher::with(['user', 'subjects', 'classes']);

        // Apply filters
        if ($request->filled('department')) {
            $query->where('department', $request->department);
        }

        if ($request->filled('is_active')) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('is_active', $request->boolean('is_active'));
            });
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('teacher_id', 'like', "%{$search}%")
                  ->orWhere('employee_id', 'like', "%{$search}%")
                  ->orWhereHas('user', function($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%")
                               ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        // Include counts
        $query->withCount(['subjects', 'classes']);

        // Paginate results
        $perPage = min($request->get('per_page', 15), 100);
        $teachers = $query->paginate($perPage);

        return TeacherResource::collection($teachers)->additional([
            'meta' => [
                'total_count' => $teachers->total(),
                'departments' => Teacher::distinct()->pluck('department')->filter(),
            ]
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Teacher::class);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'phone' => 'nullable|string|max:20',
            'date_of_birth' => 'required|date|before:today',
            'gender' => 'required|in:male,female,other',
            'teacher_id' => 'nullable|string|max:50|unique:teachers',
            'employee_id' => 'nullable|string|max:50|unique:teachers',
            'department' => 'required|string|max:100',
            'specialization' => 'nullable|string|max:100',
            'qualification' => 'nullable|string',
            'experience_years' => 'nullable|integer|min:0',
            'hire_date' => 'required|date',
            'salary' => 'nullable|numeric|min:0',
            'contract_type' => 'required|in:full_time,part_time,contract',
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

            $user->assignRole('Teacher');

            // Generate teacher ID if not provided
            $teacherId = $request->teacher_id ?: $this->generateTeacherId();

            // Create teacher
            $teacher = Teacher::create([
                'user_id' => $user->id,
                'teacher_id' => $teacherId,
                'employee_id' => $request->employee_id,
                'department' => $request->department,
                'specialization' => $request->specialization,
                'qualification' => $request->qualification,
                'experience_years' => $request->experience_years,
                'hire_date' => $request->hire_date,
                'salary' => $request->salary,
                'contract_type' => $request->contract_type,
            ]);

            // Assign subjects if provided
            if ($request->has('subjects')) {
                $teacher->subjects()->sync($request->subjects);
            }

            DB::commit();

            $teacher->load(['user', 'subjects', 'classes']);
            return new TeacherResource($teacher);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'message' => 'Failed to create teacher',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Teacher $teacher)
    {
        $this->authorize('view', $teacher);

        $teacher->load(['user', 'subjects', 'classes']);
        return new TeacherResource($teacher);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Teacher $teacher)
    {
        $this->authorize('update', $teacher);

        $validator = Validator::make($request->all(), [
            'name' => 'string|max:255',
            'email' => 'string|email|max:255|unique:users,email,' . $teacher->user_id,
            'phone' => 'nullable|string|max:20',
            'department' => 'string|max:100',
            'specialization' => 'nullable|string|max:100',
            'qualification' => 'nullable|string',
            'experience_years' => 'nullable|integer|min:0',
            'salary' => 'nullable|numeric|min:0',
            'contract_type' => 'in:full_time,part_time,contract',
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
            $userUpdateData = $request->only(['name', 'email', 'phone']);
            if (!empty($userUpdateData)) {
                $teacher->user->update($userUpdateData);
            }

            // Update teacher data
            $teacherUpdateData = $request->only([
                'department', 'specialization', 'qualification', 
                'experience_years', 'salary', 'contract_type'
            ]);
            if (!empty($teacherUpdateData)) {
                $teacher->update($teacherUpdateData);
            }

            // Update subject assignments
            if ($request->has('subjects')) {
                $teacher->subjects()->sync($request->subjects);
            }

            DB::commit();

            $teacher->load(['user', 'subjects', 'classes']);
            return new TeacherResource($teacher);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'message' => 'Failed to update teacher',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Teacher $teacher)
    {
        $this->authorize('delete', $teacher);

        $teacher->user->update(['is_active' => false]);

        return response()->json([
            'message' => 'Teacher deactivated successfully'
        ]);
    }

    /**
     * Generate a unique teacher ID.
     */
    private function generateTeacherId(): string
    {
        $prefix = 'T';
        $currentYear = now()->format('y');
        $lastTeacher = Teacher::where('teacher_id', 'like', $prefix . $currentYear . '%')
                             ->orderBy('teacher_id', 'desc')
                             ->first();

        if ($lastTeacher) {
            $lastNumber = (int) substr($lastTeacher->teacher_id, -3);
            $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '001';
        }

        return $prefix . $currentYear . $newNumber;
    }
}