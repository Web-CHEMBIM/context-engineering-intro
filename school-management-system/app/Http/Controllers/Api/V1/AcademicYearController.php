<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\AcademicYearResource;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AcademicYearController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', AcademicYear::class);

        $query = AcademicYear::with(['students', 'classes']);

        if ($request->filled('is_current')) {
            $query->where('is_current', $request->boolean('is_current'));
        }

        $query->withCount(['students', 'classes']);
        $academicYears = $query->orderBy('start_date', 'desc')->paginate($request->get('per_page', 15));

        return AcademicYearResource::collection($academicYears);
    }

    public function store(Request $request)
    {
        $this->authorize('create', AcademicYear::class);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100|unique:academic_years',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'semester_count' => 'required|integer|min:1|max:4',
            'description' => 'nullable|string',
            'is_current' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            // If this is set as current, unset others
            if ($request->get('is_current', false)) {
                AcademicYear::where('is_current', true)->update(['is_current' => false]);
            }

            $academicYear = AcademicYear::create($request->validated());
            DB::commit();

            return new AcademicYearResource($academicYear);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => 'Failed to create academic year'], 500);
        }
    }

    public function show(AcademicYear $academicYear)
    {
        $this->authorize('view', $academicYear);
        
        $academicYear->load(['students', 'classes']);
        return new AcademicYearResource($academicYear);
    }

    public function update(Request $request, AcademicYear $academicYear)
    {
        $this->authorize('update', $academicYear);

        $validator = Validator::make($request->all(), [
            'name' => 'string|max:100|unique:academic_years,name,' . $academicYear->id,
            'start_date' => 'date',
            'end_date' => 'date|after:start_date',
            'semester_count' => 'integer|min:1|max:4',
            'description' => 'nullable|string',
            'is_current' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            // If this is set as current, unset others
            if ($request->get('is_current', false) && !$academicYear->is_current) {
                AcademicYear::where('is_current', true)->update(['is_current' => false]);
            }

            $academicYear->update($request->validated());
            DB::commit();

            return new AcademicYearResource($academicYear);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => 'Failed to update academic year'], 500);
        }
    }

    public function destroy(AcademicYear $academicYear)
    {
        $this->authorize('delete', $academicYear);
        
        if ($academicYear->is_current) {
            return response()->json(['message' => 'Cannot delete current academic year'], 400);
        }

        $academicYear->update(['is_active' => false]);
        return response()->json(['message' => 'Academic year deactivated successfully']);
    }

    public function setCurrent(AcademicYear $academicYear)
    {
        $this->authorize('update', $academicYear);

        DB::beginTransaction();
        try {
            AcademicYear::where('is_current', true)->update(['is_current' => false]);
            $academicYear->update(['is_current' => true]);
            
            DB::commit();
            
            return response()->json(['message' => 'Academic year set as current successfully']);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => 'Failed to set current academic year'], 500);
        }
    }
}