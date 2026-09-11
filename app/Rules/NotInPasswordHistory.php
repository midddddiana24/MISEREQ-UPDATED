<?php

namespace App\Rules;

use App\Models\PasswordHistory;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Hash;

class NotInPasswordHistory implements ValidationRule
{
    private $userId;

    private $historyLimit = 5; // User cannot reuse last 5 passwords

    public function __construct(?int $userId = null)
    {
        $this->userId = $userId ?? (auth()->user()?->id ?? null);
    }

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $this->userId) {
            return;
        }

        $oldPassword = PasswordHistory::where('user_id', $this->userId)
            ->latest()
            ->take($this->historyLimit)
            ->get();

        foreach ($oldPassword as $history) {
            if (Hash::check($value, $history->password_hash)) {
                $fail('The :attribute cannot be one of your last 5 passwords.');

                return;
            }
        }
    }
}
