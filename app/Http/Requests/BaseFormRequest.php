<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * Base Form Request for validation & security
 *
 * All form requests should extend this base class to ensure:
 * - Proper error handling
 * - Input sanitization
 * - CSRF protection (automatic)
 * - Consistent validation responses
 */
abstract class BaseFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request
     *
     * @return array<string, ValidationRule|array|string>
     */
    abstract public function rules(): array;

    /**
     * Custom error messages for validation
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [];
    }

    /**
     * Handle a failed validation attempt
     *
     *
     * @throws HttpResponseException
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422)
        );
    }

    /**
     * Get sanitized input - removes dangerous characters
     */
    public function sanitized(string $key, mixed $default = null): mixed
    {
        $value = $this->input($key, $default);

        if (is_string($value)) {
            // Remove potential XSS vectors
            $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
            // Trim whitespace
            $value = trim($value);
        }

        return $value;
    }

    /**
     * Get all sanitized inputs
     */
    public function sanitizedAll(): array
    {
        $sanitized = [];

        foreach ($this->all() as $key => $value) {
            if (is_string($value)) {
                $sanitized[$key] = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
                $sanitized[$key] = trim($sanitized[$key]);
            } else {
                $sanitized[$key] = $value;
            }
        }

        return $sanitized;
    }
}
