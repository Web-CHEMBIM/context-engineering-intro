<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SchoolClassResource extends JsonResource
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
            'grade_level' => $this->grade_level,
            'section' => $this->section,
            'academic_year_id' => $this->academic_year_id,
            'academic_year' => new AcademicYearResource($this->whenLoaded('academicYear')),
            'class_teacher_id' => $this->class_teacher_id,
            'class_teacher' => new TeacherResource($this->whenLoaded('classTeacher')),
            'capacity' => $this->capacity,
            'room_number' => $this->room_number,
            'schedule' => $this->schedule,
            'is_active' => $this->is_active,
            'students' => StudentResource::collection($this->whenLoaded('students')),
            'teachers' => TeacherResource::collection($this->whenLoaded('teachers')),
            'subjects' => SubjectResource::collection($this->whenLoaded('subjects')),
            'students_count' => $this->when(isset($this->students_count), $this->students_count),
            'teachers_count' => $this->when(isset($this->teachers_count), $this->teachers_count),
            'subjects_count' => $this->when(isset($this->subjects_count), $this->subjects_count),
            'enrollment_rate' => $this->when(isset($this->enrollment_rate), $this->enrollment_rate),
            'average_attendance' => $this->when(isset($this->average_attendance), $this->average_attendance),
            'class_average_grade' => $this->when(isset($this->class_average_grade), $this->class_average_grade),
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