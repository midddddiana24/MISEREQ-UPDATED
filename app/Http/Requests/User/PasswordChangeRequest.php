<?php

namespace App\Http\Requests\User;

use App\Http\Requests\BaseFormRequest;
use App\Rules\NotInPasswordHistory;
use App\Rules\StrongPassword;
use Illuminate\Contracts\Validation\ValidationRule;

class PasswordChangeRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'current_password' => [
                'required',
                'string',
                'current_password',
            ],
            'new_password' => [
                'required',
                'string',
                'confirmed',
                new StrongPassword(),
                new NotInPasswordHistory(auth()->id()),
            ],
            'new_password_confirmation' => [
                'required',
                'string',
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
            'current_password.required' => 'Current password is required',
            'current_password.current_password' => 'Current password is incorrect',
            'new_password.required' => 'New password is required',
            'new_password.confirmed' => 'Passwords do not match',
            'new_password.different' => 'New password must be different from current password',
        ];
    }

    /**
     * Prepare data for validation - sanitize inputs
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        // Don't trim passwords - they should be exactly as the user entered them
    }
}
