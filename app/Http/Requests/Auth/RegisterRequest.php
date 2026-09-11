<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\BaseFormRequest;
use App\Rules\StrongPassword;

class RegisterRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'username' => [
                'required',
                'string',
                'max:255',
                'unique:users,username',
                'regex:/^[a-zA-Z0-9_\-\.]+$/',
            ],
            'email' => [
                'required',
                'email:rfc,dns',
                'unique:users,email',
                'max:255',
            ],
            'full_name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-zA-Z\s\-\']+$/',
            ],
            'password' => [
                'required',
                'string',
                'confirmed',
                new StrongPassword(),
            ],
            'password_confirmation' => [
                'required',
                'string',
            ],
            'terms' => [
                'accepted',
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
            'username.required' => 'Username is required',
            'username.unique' => 'This username is already taken',
            'username.regex' => 'Username can only contain letters, numbers, hyphens, and underscores',
            'email.required' => 'Email is required',
            'email.email' => 'Please provide a valid email address',
            'email.unique' => 'This email is already registered',
            'full_name.required' => 'Full name is required',
            'full_name.regex' => 'Full name can only contain letters, spaces, hyphens, and apostrophes',
            'password.required' => 'Password is required',
            'password.confirmed' => 'Passwords do not match',
            'terms.accepted' => 'You must accept the terms and conditions',
        ];
    }

    /**
     * Prepare data for validation - sanitize inputs
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'username' => trim($this->username ?? ''),
            'email' => strtolower(trim($this->email ?? '')),
            'full_name' => trim($this->full_name ?? ''),
            'password' => $this->password ?? '',
        ]);
    }
}
