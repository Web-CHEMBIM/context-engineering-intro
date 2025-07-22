<?php

namespace App\Http\Requests;

use App\Models\AcademicYear;
use App\Models\Teacher;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSchoolClassRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\SchoolClass::class);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'grade_level' => ['required', 'integer', 'min:1', 'max:12'],
            'section' => ['nullable', 'string', 'max:10', 'regex:/^[A-Z]$/'], // Single uppercase letter
            'academic_year_id' => ['required', 'integer', 'exists:academic_years,id'],
            'class_teacher_id' => ['nullable', 'integer', 'exists:teachers,id'],
            'capacity' => ['required', 'integer', 'min:10', 'max:50'],
            'room_number' => ['nullable', 'string', 'max:20', 'regex:/^[A-Z0-9\-]+$/'],
            'schedule' => ['nullable', 'json'],
        ];
    }

    public function messages(): array
    {
        return [
            'section.regex' => 'Section must be a single uppercase letter (A, B, C, etc.).',
            'capacity.min' => 'Class capacity must be at least 10 students.',
            'capacity.max' => 'Class capacity cannot exceed 50 students.',
            'room_number.regex' => 'Room number may only contain uppercase letters, numbers, and hyphens.',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Validate unique class name within academic year and grade level
            if ($this->filled(['name', 'academic_year_id', 'grade_level', 'section'])) {
                $exists = \App\Models\SchoolClass::where('academic_year_id', $this->academic_year_id)
                    ->where('grade_level', $this->grade_level)
                    ->where('section', $this->section)
                    ->exists();

                if ($exists) {
                    $validator->errors()->add('section', 'A class with this grade level and section already exists for this academic year.');
                }
            }

            // Validate class teacher availability
            if ($this->filled('class_teacher_id')) {
                $teacherClassCount = \App\Models\SchoolClass::where('class_teacher_id', $this->class_teacher_id)
                    ->where('is_active', true)
                    ->count();

                if ($teacherClassCount >= 2) { // Limit class teacher to 2 classes
                    $validator->errors()->add('class_teacher_id', 'This teacher is already assigned as class teacher for the maximum number of classes (2).');
                }
            }

            // Validate academic year is active
            if ($this->filled('academic_year_id')) {
                $academicYear = AcademicYear::find($this->academic_year_id);
                if ($academicYear && !$academicYear->is_active) {
                    $validator->errors()->add('academic_year_id', 'Cannot create class for inactive academic year.');
                }
            }
        });
    }
}