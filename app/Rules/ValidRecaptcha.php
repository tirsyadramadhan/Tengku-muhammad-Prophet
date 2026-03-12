<?php

namespace App\Rules;

use App\Services\RecaptchaService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidRecaptcha implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $recaptcha = new RecaptchaService();

        if (!$recaptcha->verify($value)) {
            $fail('reCAPTCHA verification failed. Please try again.');
        }
    }
}
