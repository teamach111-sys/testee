<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

/**
 * Vérifie les tokens Google reCAPTCHA v3.
 *
 * Si la clé secrète n'est pas configurée (environnement de développement),
 * la vérification est désactivée : CandidatureController peut alors être
 * testé sans reCAPTCHA.
 */
class RecaptchaService
{
    /**
     * Vérifie un token reCAPTCHA v3.
     *
     * @return bool true si le token est valide (ou si reCAPTCHA est désactivé).
     */
    public function verify(?string $token, ?string $remoteIp = null): bool
    {
        $secret = config('services.recaptcha.secret_key');
        $enabled = filter_var(config('services.recaptcha.enabled', false), FILTER_VALIDATE_BOOLEAN);

        // Dev / non configuré -> on laisse passer (reCAPTCHA désactivé).
        if (empty($secret) || ! $enabled) {
            return true;
        }

        // Token manquant alors que reCAPTCHA est activé -> rejet.
        if (empty($token)) {
            return false;
        }

        try {
            $response = Http::asForm()->timeout(10)->post(
                'https://www.google.com/recaptcha/api/siteverify',
                [
                    'secret'   => $secret,
                    'response' => $token,
                    'remoteip' => $remoteIp,
                ]
            );

            $body = $response->json();

            if (! $response->successful() || ($body['success'] ?? false) !== true) {
                return false;
            }

            $score = (float) ($body['score'] ?? 0.0);
            return $score >= (float) config('services.recaptcha.min_score', 0.5);
        } catch (\Throwable $e) {
            \Log::warning('reCAPTCHA verification failed: ' . $e->getMessage());
            return false;
        }
    }
}
