<?php

namespace App\Http\Requests\User;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Contracts\Validation\ValidationRule;

class ProfileUpdateRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        $userId = auth()->id();

        return [
            'full_name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-zA-Z\s\-\']+$/',
            ],
            'email' => [
                'required',
                'email:rfc,dns',
                'max:255',
                "unique:users,email,{$userId}",
            ],
            'phone' => [
                'nullable',
                'string',
                'max:20',
                'regex:/^[\d\+\-\(\)\s]+$/',
            ],
            'department' => [
                'nullable',
                'string',
                'max:255',
            ],
            'bio' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ];
    }

    /**
     * Custom validation messages
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'full_name.required' => 'Full name is required',
            'full_name.regex' => 'Full name can only contain letters, spaces, hyphens, and apostrophes',
            'email.required' => 'Email is required',
            'email.email' => 'Please provide a valid email address',
            'email.unique' => 'This email is already in use',
            'phone.regex' => 'Phone number contains invalid characters',
            'department.max' => 'Department name is too long',
            'bio.max' => 'Biography must not exceed 1000 characters',
        ];
    }

    /**
     * Prepare data for validation - sanitize inputs
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'full_name' => trim($this->full_name ?? ''),
            'email' => strtolower(trim($this->email ?? '')),
            'phone' => trim($this->phone ?? ''),
            'department' => trim($this->department ?? ''),
            'bio' => trim($this->bio ?? ''),
        ]);
    }
}
