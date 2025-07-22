<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

abstract class BaseFormRequest extends FormRequest
{
    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator)
    {
        if ($this->expectsJson()) {
            throw new HttpResponseException(
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
     * Common validation rules for names.
     */
    protected function nameRules(bool $required = true): array
    {
        $rules = ['string', 'max:255', 'regex:/^[a-zA-Z\s\.\-\']+$/'];
        
        if ($required) {
            array_unshift($rules, 'required');
        } else {
            array_unshift($rules, 'nullable');
        }

        return $rules;
    }

    /**
     * Common validation rules for emails.
     */
    protected function emailRules(bool $unique = true, ?int $ignoreId = null): array
    {
        $rules = ['required', 'string', 'email:rfc,dns', 'max:255'];
        
        if ($unique) {
            if ($ignoreId) {
                $rules[] = "unique:users,email,{$ignoreId}";
            } else {
                $rules[] = 'unique:users,email';
            }
        }

        return $rules;
    }

    /**
     * Common validation rules for phone numbers.
     */
    protected function phoneRules(bool $unique = true, ?int $ignoreId = null): array
    {
        $rules = ['nullable', 'string', 'regex:/^\+?[1-9]\d{1,14}$/'];
        
        if ($unique) {
            if ($ignoreId) {
                $rules[] = "unique:users,phone,{$ignoreId}";
            } else {
                $rules[] = 'unique:users,phone';
            }
        }

        return $rules;
    }

    /**
     * Common validation rules for profile photos.
     */
    protected function profilePhotoRules(): array
    {
        return [
            'nullable',
            'image',
            'mimes:jpeg,png,jpg,gif,webp',
            'max:2048', // 2MB
            'dimensions:min_width=100,min_height=100,max_width=2000,max_height=2000',
        ];
    }

    /**
     * Common validation rules for dates of birth.
     */
    protected function dateOfBirthRules(int $minAge = 0, int $maxAge = 100): array
    {
        return [
            'required',
            'date',
            'before:today',
            'before:' . now()->subYears($minAge)->format('Y-m-d'),
            'after:' . now()->subYears($maxAge)->format('Y-m-d'),
        ];
    }

    /**
     * Common validation rules for academic identifiers.
     */
    protected function academicIdRules(string $table, string $column, bool $required = false): array
    {
        $rules = ['string', 'max:50', "unique:{$table},{$column}", 'regex:/^[A-Z0-9]+$/'];
        
        if ($required) {
            array_unshift($rules, 'required');
        } else {
            array_unshift($rules, 'nullable');
        }

        return $rules;
    }

    /**
     * Get common error messages.
     */
    protected function commonMessages(): array
    {
        return [
            '*.required' => 'This field is required.',
            '*.string' => 'This field must be a valid text.',
            '*.email' => 'Please provide a valid email address.',
            '*.unique' => 'This value is already taken.',
            '*.regex' => 'The format is invalid.',
            '*.max' => 'This field is too long.',
            '*.min' => 'This field is too short.',
            '*.before' => 'This date must be before today.',
            '*.after' => 'This date is invalid.',
            '*.image' => 'Please upload a valid image file.',
            '*.mimes' => 'Invalid file type.',
            '*.dimensions' => 'Image dimensions must be between 100x100 and 2000x2000 pixels.',
        ];
    }

    /**
     * Sanitize input data.
     */
    protected function prepareForValidation()
    {
        // Clean up common fields
        if ($this->has('name')) {
            $this->merge(['name' => trim($this->name)]);
        }

        if ($this->has('email')) {
            $this->merge(['email' => strtolower(trim($this->email))]);
        }

        if ($this->has('phone')) {
            // Remove all non-numeric characters except + at the beginning
            $phone = preg_replace('/[^\+\d]/', '', $this->phone);
            if ($phone) {
                $this->merge(['phone' => $phone]);
            }
        }
    }
}