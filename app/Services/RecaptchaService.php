<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class RecaptchaService
{
    public function verify(string $token): bool
    {
        $response = Http::withoutVerifying()
            ->asForm()
            ->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret'   => config('services.recaptcha.secret_key'),
                'response' => $token,
                'remoteip' => request()->ip(),
            ]);

        $data = $response->json();

        // v2 only needs success check — no score
        return isset($data['success']) && $data['success'] === true;
    }
}
