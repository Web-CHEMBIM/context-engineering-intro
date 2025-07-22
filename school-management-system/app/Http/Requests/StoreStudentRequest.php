<?php

namespace App\Http\Requests;

use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\Subject;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStudentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Student::class);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            // User information
            'name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-zA-Z\s\.\-\']+$/',
            ],
            'email' => [
                'required',
                'string',
                'email:rfc,dns',
                'max:255',
                'unique:users,email',
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],
            'phone' => [
                'nullable',
                'string',
                'regex:/^\+?[1-9]\d{1,14}$/',
                'unique:users,phone',
            ],
            'date_of_birth' => [
                'required',
                'date',
                'before:today',
                'after:' . now()->subYears(30)->format('Y-m-d'), // Max 30 years old
            ],
            'gender' => [
                'required',
                'string',
                Rule::in(['male', 'female', 'other']),
            ],
            'address' => [
                'nullable',
                'string',
                'max:500',
            ],
            'profile_photo' => [
                'nullable',
                'image',
                'mimes:jpeg,png,jpg,gif',
                'max:2048',
            ],

            // Student-specific information
            'student_id' => [
                'nullable',
                'string',
                'max:50',
                'unique:students,student_id',
                'regex:/^[A-Z0-9]+$/', // Only uppercase letters and numbers
            ],
            'academic_year_id' => [
                'required',
                'integer',
                'exists:academic_years,id',
            ],
            'admission_date' => [
                'required',
                'date',
                'before_or_equal:today',
                'after_or_equal:' . now()->subYear()->format('Y-m-d'), // Within last year
            ],
            'emergency_contact_name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-zA-Z\s\.\-\']+$/',
            ],
            'emergency_contact_phone' => [
                'required',
                'string',
                'regex:/^\+?[1-9]\d{1,14}$/',
            ],
            'medical_conditions' => [
                'nullable',
                'string',
                'max:1000',
            ],
            'total_fees' => [
                'required',
                'numeric',
                'min:0',
                'max:999999.99',
            ],
            'fees_paid' => [
                'nullable',
                'numeric',
                'min:0',
                'lte:total_fees', // Less than or equal to total fees
            ],

            // Enrollment information
            'classes' => [
                'required',
                'array',
                'min:1',
                'max:10', // Maximum 10 classes
            ],
            'classes.*' => [
                'integer',
                'exists:school_classes,id',
            ],
            'subjects' => [
                'required',
                'array',
                'min:3', // Minimum 3 subjects
                'max:15', // Maximum 15 subjects
            ],
            'subjects.*' => [
                'integer',
                'exists:subjects,id',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.regex' => 'The name may only contain letters, spaces, dots, hyphens, and apostrophes.',
            'email.unique' => 'This email address is already registered.',
            'phone.unique' => 'This phone number is already registered.',
            'date_of_birth.after' => 'Students must be younger than 30 years old.',
            'student_id.regex' => 'Student ID may only contain uppercase letters and numbers.',
            'student_id.unique' => 'This student ID is already taken.',
            'admission_date.after_or_equal' => 'Admission date cannot be more than one year ago.',
            'emergency_contact_name.required' => 'Emergency contact name is required for student safety.',
            'emergency_contact_phone.required' => 'Emergency contact phone is required for student safety.',
            'emergency_contact_name.regex' => 'Emergency contact name may only contain letters, spaces, dots, hyphens, and apostrophes.',
            'total_fees.required' => 'Total fees amount is required.',
            'fees_paid.lte' => 'Fees paid cannot exceed total fees.',
            'classes.required' => 'Please select at least one class for the student.',
            'classes.max' => 'A student cannot be enrolled in more than 10 classes.',
            'subjects.min' => 'Please select at least 3 subjects for the student.',
            'subjects.max' => 'A student cannot be enrolled in more than 15 subjects.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $this->validateAcademicYearConstraints($validator);
            $this->validateClassCapacity($validator);
            $this->validateSubjectGradeLevelCompatibility($validator);
            $this->validateClassSubjectAlignment($validator);
            $this->validateAgeGradeCompatibility($validator);
        });
    }

    /**
     * Validate academic year constraints.
     */
    protected function validateAcademicYearConstraints($validator)
    {
        if ($this->filled('academic_year_id')) {
            $academicYear = AcademicYear::find($this->academic_year_id);
            
            if ($academicYear && !$academicYear->is_active) {
                $validator->errors()->add('academic_year_id', 'Cannot enroll student in inactive academic year.');
            }

            if ($academicYear && $this->filled('admission_date')) {
                $admissionDate = $this->admission_date;
                
                if ($admissionDate < $academicYear->start_date) {
                    $validator->errors()->add('admission_date', 'Admission date cannot be before academic year start date.');
                }

                if ($admissionDate > $academicYear->end_date) {
                    $validator->errors()->add('admission_date', 'Admission date cannot be after academic year end date.');
                }
            }
        }
    }

    /**
     * Validate class capacity constraints.
     */
    protected function validateClassCapacity($validator)
    {
        if ($this->filled('classes')) {
            foreach ($this->classes as $classId) {
                $class = SchoolClass::withCount('students')->find($classId);
                
                if ($class && $class->students_count >= $class->capacity) {
                    $validator->errors()->add('classes', "Class '{$class->name}' is at full capacity ({$class->capacity} students).");
                }
            }
        }
    }

    /**
     * Validate subject and grade level compatibility.
     */
    protected function validateSubjectGradeLevelCompatibility($validator)
    {
        if ($this->filled(['classes', 'subjects'])) {
            $classGradeLevels = SchoolClass::whereIn('id', $this->classes)->pluck('grade_level')->unique()->toArray();
            
            foreach ($this->subjects as $subjectId) {
                $subject = Subject::find($subjectId);
                
                if ($subject && $subject->grade_levels) {
                    $subjectGradeLevels = $subject->grade_levels;
                    $compatibleGrades = array_intersect($classGradeLevels, $subjectGradeLevels);
                    
                    if (empty($compatibleGrades)) {
                        $validator->errors()->add('subjects', "Subject '{$subject->name}' is not available for the selected grade levels.");
                    }
                }
            }
        }
    }

    /**
     * Validate class and subject alignment.
     */
    protected function validateClassSubjectAlignment($validator)
    {
        if ($this->filled(['classes', 'subjects'])) {
            // Check if selected subjects are offered by selected classes
            $classSubjects = SchoolClass::whereIn('id', $this->classes)
                ->with('subjects')
                ->get()
                ->pluck('subjects')
                ->flatten()
                ->pluck('id')
                ->unique()
                ->toArray();

            $unavailableSubjects = array_diff($this->subjects, $classSubjects);
            
            if (!empty($unavailableSubjects)) {
                $subjectNames = Subject::whereIn('id', $unavailableSubjects)->pluck('name')->toArray();
                $validator->errors()->add('subjects', 'The following subjects are not offered by the selected classes: ' . implode(', ', $subjectNames));
            }
        }
    }

    /**
     * Validate age and grade compatibility.
     */
    protected function validateAgeGradeCompatibility($validator)
    {
        if ($this->filled(['date_of_birth', 'classes'])) {
            $age = now()->diffInYears($this->date_of_birth);
            $gradeLevels = SchoolClass::whereIn('id', $this->classes)->pluck('grade_level')->toArray();
            $maxGradeLevel = max($gradeLevels);
            
            // General age-grade guidelines
            $expectedAge = $maxGradeLevel + 5; // Assuming grade 1 starts at age 6
            
            if ($age > $expectedAge + 3) { // Allow 3 years flexibility
                $validator->errors()->add('date_of_birth', "Student age ({$age}) seems too high for grade level {$maxGradeLevel}. Please verify the information.");
            }
            
            if ($age < $expectedAge - 2) { // Allow 2 years flexibility for younger students
                $validator->errors()->add('date_of_birth', "Student age ({$age}) seems too low for grade level {$maxGradeLevel}. Please verify the information.");
            }
        }
    }
}