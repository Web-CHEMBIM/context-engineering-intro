<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeacherResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'teacher_id' => $this->teacher_id,
            'employee_id' => $this->employee_id,
            'user' => new UserResource($this->whenLoaded('user')),
            'user_id' => $this->user_id,
            'department' => $this->department,
            'specialization' => $this->specialization,
            'qualification' => $this->qualification,
            'experience_years' => $this->experience_years,
            'hire_date' => $this->hire_date?->format('Y-m-d'),
            'salary' => $this->when($request->user()?->hasRole(['SuperAdmin', 'Admin']), $this->salary),
            'contract_type' => $this->contract_type,
            'is_head_of_department' => $this->is_head_of_department,
            'office_hours' => $this->office_hours,
            'subjects' => SubjectResource::collection($this->whenLoaded('subjects')),
            'classes' => SchoolClassResource::collection($this->whenLoaded('classes')),
            'subjects_count' => $this->when(isset($this->subjects_count), $this->subjects_count),
            'classes_count' => $this->when(isset($this->classes_count), $this->classes_count),
            'total_students' => $this->when(isset($this->total_students), $this->total_students),
            'workload_hours' => $this->when(isset($this->workload_hours), $this->workload_hours),
            'performance_rating' => $this->when(isset($this->performance_rating), $this->performance_rating),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Get additional data that should be returned with the resource array.
     *
     * @return array<string, mixed>
     */
    public function with(Request $request): array
    {
        return [
            'meta' => [
                'version' => '1.0',
                'timestamp' => now()->toISOString(),
            ],
        ];
    }
}