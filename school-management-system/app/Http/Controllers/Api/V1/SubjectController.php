<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\SubjectResource;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class SubjectController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Subject::class);

        $query = Subject::with(['teachers', 'students', 'classes']);

        if ($request->filled('department')) {
            $query->where('department', $request->department);
        }

        if ($request->filled('grade_level')) {
            $query->whereJsonContains('grade_levels', (int)$request->grade_level);
        }

        $query->withCount(['teachers', 'students', 'classes']);
        $subjects = $query->paginate($request->get('per_page', 15));

        return SubjectResource::collection($subjects);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Subject::class);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:20|unique:subjects',
            'description' => 'nullable|string',
            'credit_hours' => 'required|integer|min:1',
            'department' => 'required|string|max:100',
            'grade_levels' => 'required|array',
            'grade_levels.*' => 'integer|min:1|max:12',
            'is_mandatory' => 'boolean',
            'prerequisites' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        $subject = Subject::create($request->validated());
        return new SubjectResource($subject);
    }

    public function show(Subject $subject)
    {
        $this->authorize('view', $subject);
        
        $subject->load(['teachers', 'students', 'classes']);
        return new SubjectResource($subject);
    }

    public function update(Request $request, Subject $subject)
    {
        $this->authorize('update', $subject);

        $validator = Validator::make($request->all(), [
            'name' => 'string|max:100',
            'code' => 'string|max:20|unique:subjects,code,' . $subject->id,
            'description' => 'nullable|string',
            'credit_hours' => 'integer|min:1',
            'department' => 'string|max:100',
            'grade_levels' => 'array',
            'grade_levels.*' => 'integer|min:1|max:12',
            'is_mandatory' => 'boolean',
            'prerequisites' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        $subject->update($request->validated());
        return new SubjectResource($subject);
    }

    public function destroy(Subject $subject)
    {
        $this->authorize('delete', $subject);
        
        $subject->update(['is_active' => false]);
        return response()->json(['message' => 'Subject deactivated successfully']);
    }
}