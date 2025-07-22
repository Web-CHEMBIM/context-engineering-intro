<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTeacherRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Teacher::class);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z\s\.\-\']+$/'],
            'email' => ['required', 'string', 'email:rfc,dns', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'phone' => ['nullable', 'string', 'regex:/^\+?[1-9]\d{1,14}$/', 'unique:users,phone'],
            'date_of_birth' => ['required', 'date', 'before:' . now()->subYears(18)->format('Y-m-d'), 'after:1950-01-01'],
            'gender' => ['required', 'string', Rule::in(['male', 'female', 'other'])],
            
            // Teacher-specific fields
            'teacher_id' => ['nullable', 'string', 'max:50', 'unique:teachers,teacher_id'],
            'employee_id' => ['nullable', 'string', 'max:50', 'unique:teachers,employee_id'],
            'department' => ['required', 'string', 'max:100'],
            'specialization' => ['nullable', 'string', 'max:100'],
            'qualification' => ['required', 'string', 'max:500'],
            'experience_years' => ['nullable', 'integer', 'min:0', 'max:50'],
            'hire_date' => ['required', 'date', 'before_or_equal:today', 'after:1990-01-01'],
            'salary' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'contract_type' => ['required', 'string', Rule::in(['full_time', 'part_time', 'contract'])],
            'subjects' => ['required', 'array', 'min:1'],
            'subjects.*' => ['integer', 'exists:subjects,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'date_of_birth.before' => 'Teachers must be at least 18 years old.',
            'qualification.required' => 'Teacher qualification is required.',
            'subjects.required' => 'Please assign at least one subject to the teacher.',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->filled(['hire_date', 'date_of_birth'])) {
                $ageAtHire = $this->date_of_birth->diffInYears($this->hire_date);
                if ($ageAtHire < 18) {
                    $validator->errors()->add('hire_date', 'Teacher must be at least 18 years old at time of hiring.');
                }
            }
        });
    }
}