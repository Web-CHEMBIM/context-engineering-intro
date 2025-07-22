<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubjectResource extends JsonResource
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
            'name' => $this->name,
            'code' => $this->code,
            'description' => $this->description,
            'credit_hours' => $this->credit_hours,
            'department' => $this->department,
            'grade_levels' => $this->grade_levels,
            'is_mandatory' => $this->is_mandatory,
            'prerequisites' => $this->prerequisites,
            'syllabus_file' => $this->syllabus_file,
            'is_active' => $this->is_active,
            'teachers' => TeacherResource::collection($this->whenLoaded('teachers')),
            'students' => StudentResource::collection($this->whenLoaded('students')),
            'classes' => SchoolClassResource::collection($this->whenLoaded('classes')),
            'teachers_count' => $this->when(isset($this->teachers_count), $this->teachers_count),
            'students_count' => $this->when(isset($this->students_count), $this->students_count),
            'classes_count' => $this->when(isset($this->classes_count), $this->classes_count),
            'enrollment_capacity' => $this->when(isset($this->enrollment_capacity), $this->enrollment_capacity),
            'average_grade' => $this->when(isset($this->average_grade), $this->average_grade),
            'pass_rate' => $this->when(isset($this->pass_rate), $this->pass_rate),
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