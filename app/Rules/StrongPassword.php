<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class StrongPassword implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Check minimum length (12 characters)
        if (strlen($value) < 12) {
            $fail('The :attribute must be at least 12 characters long.');

            return;
        }

        // Check for uppercase letters
        if (! preg_match('/[A-Z]/', $value)) {
            $fail('The :attribute must contain at least one uppercase letter.');

            return;
        }

        // Check for lowercase letters
        if (! preg_match('/[a-z]/', $value)) {
            $fail('The :attribute must contain at least one lowercase letter.');

            return;
        }

        // Check for numbers
        if (! preg_match('/[0-9]/', $value)) {
            $fail('The :attribute must contain at least one number.');

            return;
        }

        // Check for special characters
        if (! preg_match('/[!@#$%^&*()_\-+=\[\]{};:\'",.<>?\/\\|`~]/', $value)) {
            $fail('The :attribute must contain at least one special character (!@#$%^&*...).');

            return;
        }

        // Check for common patterns (consecutive characters, repeated characters)
        if (preg_match('/(.)\1{2,}/', $value)) {
            $fail('The :attribute cannot contain more than 2 consecutive identical characters.');

            return;
        }
    }
}
