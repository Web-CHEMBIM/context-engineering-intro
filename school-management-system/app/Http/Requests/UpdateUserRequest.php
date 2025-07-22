<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->route('user');
        return $this->user()->can('update', $user);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userId = $this->route('user')->id;

        return [
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                'regex:/^[a-zA-Z\s\.\-\']+$/',
            ],
            'email' => [
                'sometimes',
                'required',
                'string',
                'email:rfc,dns',
                'max:255',
                Rule::unique('users')->ignore($userId),
            ],
            'password' => [
                'nullable',
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
                'regex:/^\+?[1-9]\d{1,14}$/',
                Rule::unique('users')->ignore($userId),
            ],
            'date_of_birth' => [
                'sometimes',
                'required',
                'date',
                'before:today',
                'after:1900-01-01',
            ],
            'gender' => [
                'sometimes',
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
                'dimensions:min_width=100,min_height=100,max_width=2000,max_height=2000',
            ],
            'is_active' => [
                'sometimes',
                'boolean',
            ],
            'roles' => [
                'sometimes',
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
     */
    public function messages(): array
    {
        return [
            'name.regex' => 'The name may only contain letters, spaces, dots, hyphens, and apostrophes.',
            'email.unique' => 'This email address is already taken by another user.',
            'phone.unique' => 'This phone number is already taken by another user.',
            'password.uncompromised' => 'The password has been compromised. Please choose a different password.',
            'profile_photo.dimensions' => 'Profile photo must be between 100x100 and 2000x2000 pixels.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $user = $this->route('user');

            // Prevent self-deactivation for admins
            if ($this->has('is_active') && !$this->is_active) {
                if ($user->id === $this->user()->id && $user->hasRole(['Admin', 'SuperAdmin'])) {
                    $validator->errors()->add('is_active', 'You cannot deactivate your own account.');
                }
            }

            // Prevent role changes that would leave system without SuperAdmin
            if ($this->filled('roles')) {
                $currentSuperAdmins = \App\Models\User::role('SuperAdmin')
                    ->where('is_active', true)
                    ->where('id', '!=', $user->id)
                    ->count();

                if ($user->hasRole('SuperAdmin') && !in_array('SuperAdmin', $this->roles) && $currentSuperAdmins === 0) {
                    $validator->errors()->add('roles', 'Cannot remove SuperAdmin role. At least one active SuperAdmin must exist.');
                }
            }

            // Age validation based on new roles
            if ($this->filled('roles') && ($this->filled('date_of_birth') || $user->date_of_birth)) {
                $dateOfBirth = $this->date_of_birth ?? $user->date_of_birth;
                $age = now()->diffInYears($dateOfBirth);

                if (in_array('Teacher', $this->roles) && $age < 18) {
                    $validator->errors()->add('roles', 'User must be at least 18 years old to be assigned Teacher role.');
                }

                if (in_array('Admin', $this->roles) && $age < 21) {
                    $validator->errors()->add('roles', 'User must be at least 21 years old to be assigned Admin role.');
                }
            }
        });
    }
}