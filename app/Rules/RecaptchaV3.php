<?php

namespace App\Rules;

use Closure;
use Illuminate\Support\Facades\Http;
use Illuminate\Contracts\Validation\ValidationRule;

class RecaptchaV3 implements ValidationRule
{
    public function __construct(
        private string $action,
        private ?float $threshold = null // <-- nullable
    ) {
        $this->threshold ??= (float) config('services.recaptcha.score', 0.5);
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (app()->environment('testing')) return;

        $secret = config('services.recaptcha.secret');
        if (!$secret) {
            $fail('reCAPTCHA is not configured.');
            return;
        }

        $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret'   => $secret,
            'response' => $value,
            'remoteip' => request()->ip(),
        ]);

        if (!$response->ok()) {
            $fail('reCAPTCHA verification failed.');
            return;
        }

        $data = $response->json();

        if (empty($data['success'])) {
            $fail('reCAPTCHA check was not successful.');
            return;
        }

        if (($data['action'] ?? null) !== $this->action) {
            $fail('reCAPTCHA action mismatch.');
            return;
        }

        if (($data['score'] ?? 0) < $this->threshold) {
            $fail('reCAPTCHA score too low.');
        }
    }
}
