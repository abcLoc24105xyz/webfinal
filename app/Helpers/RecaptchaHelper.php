<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;

function verifyRecaptcha($token)
{
    $secret = env('RECAPTCHA_SECRET_KEY');

    // Luôn cho qua ở local
    if (app()->environment('local')) {
        return true;
    }

    if (empty($token) || empty($secret)) {
        return false;
    }

    try {
        $response = Http::post('https://www.google.com/recaptcha/api/siteverify', [
            'secret'   => $secret,
            'response' => $token,
        ]);

        $data = $response->json();

        // Score < 0.5 = bot → trả về false
        return $data['success'] && ($data['score'] ?? 1.0) >= 0.5;
    } catch (\Exception $e) {
        return false;
    }
}