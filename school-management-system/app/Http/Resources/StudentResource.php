<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentResource extends JsonResource
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
            'student_id' => $this->student_id,
            'user' => new UserResource($this->whenLoaded('user')),
            'user_id' => $this->user_id,
            'admission_date' => $this->admission_date?->format('Y-m-d'),
            'academic_year_id' => $this->academic_year_id,
            'academic_year' => new AcademicYearResource($this->whenLoaded('academicYear')),
            'emergency_contact_name' => $this->emergency_contact_name,
            'emergency_contact_phone' => $this->emergency_contact_phone,
            'medical_conditions' => $this->medical_conditions,
            'fees_paid' => $this->fees_paid,
            'fees_pending' => $this->fees_pending,
            'total_fees' => $this->total_fees,
            'classes' => SchoolClassResource::collection($this->whenLoaded('classes')),
            'subjects' => SubjectResource::collection($this->whenLoaded('subjects')),
            'classes_count' => $this->when(isset($this->classes_count), $this->classes_count),
            'subjects_count' => $this->when(isset($this->subjects_count), $this->subjects_count),
            'attendance_rate' => $this->when(isset($this->attendance_rate), $this->attendance_rate),
            'grade_average' => $this->when(isset($this->grade_average), $this->grade_average),
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