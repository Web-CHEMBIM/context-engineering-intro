<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\User::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-zA-Z\s\.\-\']+$/', // Only letters, spaces, dots, hyphens, apostrophes
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
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised(),
                'confirmed',
            ],
            'phone' => [
                'nullable',
                'string',
                'regex:/^\+?[1-9]\d{1,14}$/', // E.164 format
                'unique:users,phone',
            ],
            'date_of_birth' => [
                'required',
                'date',
                'before:today',
                'after:1900-01-01',
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
                'max:2048', // 2MB
                'dimensions:min_width=100,min_height=100,max_width=2000,max_height=2000',
            ],
            'is_active' => [
                'boolean',
            ],
            'roles' => [
                'required',
                'array',
                'min:1',
            ],
            'roles.*' => [
                'string',
                'exists:roles,name',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.regex' => 'The name may only contain letters, spaces, dots, hyphens, and apostrophes.',
            'email.email' => 'Please provide a valid email address.',
            'email.unique' => 'This email address is already registered in the system.',
            'password.uncompromised' => 'The password has been compromised in a data breach. Please choose a different password.',
            'phone.regex' => 'Please provide a valid phone number.',
            'phone.unique' => 'This phone number is already registered in the system.',
            'date_of_birth.before' => 'The date of birth must be before today.',
            'date_of_birth.after' => 'Please provide a valid date of birth.',
            'profile_photo.dimensions' => 'The profile photo dimensions must be between 100x100 and 2000x2000 pixels.',
            'roles.required' => 'Please assign at least one role to the user.',
            'roles.*.exists' => 'One or more selected roles are invalid.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'date_of_birth' => 'date of birth',
            'profile_photo' => 'profile photo',
            'is_active' => 'active status',
        ];
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        if ($this->expectsJson()) {
            throw new \Illuminate\Http\Exceptions\HttpResponseException(
                response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                    'status_code' => 422
                ], 422)
            );
        }

        parent::failedValidation($validator);
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Additional custom validation logic
            
            // Check age restrictions based on role
            if ($this->filled('roles') && $this->filled('date_of_birth')) {
                $age = now()->diffInYears($this->date_of_birth);
                
                if (in_array('Student', $this->roles) && $age > 25) {
                    $validator->errors()->add('date_of_birth', 'Students must be 25 years old or younger.');
                }
                
                if (in_array('Teacher', $this->roles) && $age < 18) {
                    $validator->errors()->add('date_of_birth', 'Teachers must be at least 18 years old.');
                }
                
                if (in_array('Admin', $this->roles) && $age < 21) {
                    $validator->errors()->add('date_of_birth', 'Administrators must be at least 21 years old.');
                }
            }

            // Validate role combinations
            if ($this->filled('roles') && count($this->roles) > 1) {
                $restrictedCombinations = [
                    ['Student', 'Teacher'],
                    ['Student', 'Admin'],
                    ['Student', 'SuperAdmin'],
                ];

                foreach ($restrictedCombinations as $combination) {
                    if (count(array_intersect($this->roles, $combination)) === count($combination)) {
                        $validator->errors()->add('roles', 'Invalid role combination: ' . implode(' + ', $combination));
                    }
                }
            }
        });
    }
}