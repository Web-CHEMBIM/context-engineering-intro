<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AcademicYearResource extends JsonResource
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
            'start_date' => $this->start_date?->format('Y-m-d'),
            'end_date' => $this->end_date?->format('Y-m-d'),
            'is_current' => $this->is_current,
            'semester_count' => $this->semester_count,
            'total_weeks' => $this->total_weeks,
            'holidays' => $this->holidays,
            'description' => $this->description,
            'is_active' => $this->is_active,
            'students' => StudentResource::collection($this->whenLoaded('students')),
            'classes' => SchoolClassResource::collection($this->whenLoaded('classes')),
            'students_count' => $this->when(isset($this->students_count), $this->students_count),
            'classes_count' => $this->when(isset($this->classes_count), $this->classes_count),
            'enrollment_statistics' => $this->when(isset($this->enrollment_statistics), $this->enrollment_statistics),
            'academic_performance' => $this->when(isset($this->academic_performance), $this->academic_performance),
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