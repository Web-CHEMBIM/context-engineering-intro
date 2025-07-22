<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\SchoolClassResource;
use App\Models\SchoolClass;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class SchoolClassController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', SchoolClass::class);

        $query = SchoolClass::with(['academicYear', 'classTeacher', 'students', 'teachers', 'subjects']);

        if ($request->filled('grade_level')) {
            $query->where('grade_level', $request->grade_level);
        }

        if ($request->filled('academic_year_id')) {
            $query->where('academic_year_id', $request->academic_year_id);
        }

        $query->withCount(['students', 'teachers', 'subjects']);
        $classes = $query->paginate($request->get('per_page', 15));

        return SchoolClassResource::collection($classes);
    }

    public function store(Request $request)
    {
        $this->authorize('create', SchoolClass::class);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'grade_level' => 'required|integer|min:1|max:12',
            'section' => 'nullable|string|max:10',
            'academic_year_id' => 'required|exists:academic_years,id',
            'class_teacher_id' => 'nullable|exists:teachers,id',
            'capacity' => 'required|integer|min:1',
            'room_number' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        $class = SchoolClass::create($request->validated());
        return new SchoolClassResource($class);
    }

    public function show(SchoolClass $schoolClass)
    {
        $this->authorize('view', $schoolClass);
        
        $schoolClass->load(['academicYear', 'classTeacher', 'students', 'teachers', 'subjects']);
        return new SchoolClassResource($schoolClass);
    }

    public function update(Request $request, SchoolClass $schoolClass)
    {
        $this->authorize('update', $schoolClass);

        $validator = Validator::make($request->all(), [
            'name' => 'string|max:100',
            'grade_level' => 'integer|min:1|max:12',
            'section' => 'nullable|string|max:10',
            'class_teacher_id' => 'nullable|exists:teachers,id',
            'capacity' => 'integer|min:1',
            'room_number' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        $schoolClass->update($request->validated());
        return new SchoolClassResource($schoolClass);
    }

    public function destroy(SchoolClass $schoolClass)
    {
        $this->authorize('delete', $schoolClass);
        
        $schoolClass->update(['is_active' => false]);
        return response()->json(['message' => 'Class deactivated successfully']);
    }
}