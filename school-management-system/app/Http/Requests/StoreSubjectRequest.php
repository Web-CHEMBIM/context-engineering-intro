<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSubjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Subject::class);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'code' => ['required', 'string', 'max:20', 'unique:subjects,code', 'regex:/^[A-Z0-9]+$/'],
            'description' => ['nullable', 'string', 'max:1000'],
            'credit_hours' => ['required', 'integer', 'min:1', 'max:10'],
            'department' => ['required', 'string', 'max:100'],
            'grade_levels' => ['required', 'array', 'min:1'],
            'grade_levels.*' => ['integer', 'min:1', 'max:12'],
            'is_mandatory' => ['boolean'],
            'prerequisites' => ['nullable', 'string', 'max:500'],
            'syllabus_file' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:5120'], // 5MB
        ];
    }

    public function messages(): array
    {
        return [
            'code.regex' => 'Subject code may only contain uppercase letters and numbers.',
            'code.unique' => 'This subject code is already taken.',
            'grade_levels.required' => 'Please select at least one grade level for this subject.',
            'syllabus_file.max' => 'Syllabus file size cannot exceed 5MB.',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Validate grade level sequence for multi-level subjects
            if ($this->filled('grade_levels') && count($this->grade_levels) > 1) {
                $gradeLevels = $this->grade_levels;
                sort($gradeLevels);
                
                // Check for reasonable grade level progression
                $maxGap = max($gradeLevels) - min($gradeLevels);
                if ($maxGap > 5) {
                    $validator->errors()->add('grade_levels', 'Grade level span is too wide. Consider creating separate subjects.');
                }
            }

            // Validate credit hours based on subject type
            if ($this->filled(['credit_hours', 'department'])) {
                $department = strtolower($this->department);
                
                // Core subjects should have more credit hours
                if (in_array($department, ['mathematics', 'english', 'science']) && $this->credit_hours < 3) {
                    $validator->errors()->add('credit_hours', 'Core subjects should have at least 3 credit hours.');
                }
            }
        });
    }
}