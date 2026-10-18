<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class WorkEmail implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $domain = strtolower((string) config('hotel.staff_email_domain'));

        if (! is_string($value) || ! str_ends_with(strtolower($value), '@'.$domain)) {
            $fail("Invalid email. Please use your work email ending in @{$domain}.");
        }
    }
}
