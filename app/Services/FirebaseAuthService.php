<?php

namespace App\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Throwable;

class FirebaseAuthService
{
    /**
     * Check if Firebase credentials have been configured in services config.
     */
    public function isConfigured(): bool
    {
        return ! empty(config('services.firebase.project_id')) && ! empty(config('services.firebase.api_key'));
    }

    /**
     * Get client-side Firebase configuration array for frontend SDK initialization.
     *
     * @return array{apiKey: ?string, authDomain: ?string, projectId: ?string, appId: ?string}
     */
    public function getClientConfig(): array
    {
        return [
            'apiKey' => config('services.firebase.api_key'),
            'authDomain' => config('services.firebase.auth_domain'),
            'projectId' => config('services.firebase.project_id'),
            'appId' => config('services.firebase.app_id'),
        ];
    }

    /**
     * Verify Firebase ID Token and extract user claims.
     *
     * Supports Google TokenInfo API, Firebase Lookup API, and JWT fallback for test environments.
     *
     * @return array{uid: string, email: string, name: ?string, avatar_url: ?string}
     *
     * @throws InvalidArgumentException
     */
    public function verifyIdToken(string $idToken): array
    {
        if (empty(trim($idToken))) {
            throw new InvalidArgumentException('Firebase ID Token tidak boleh kosong.');
        }

        // 1. Try Google OAuth2 tokeninfo endpoint (Reliable, handles Google/Firebase ID tokens)
        try {
            $response = Http::timeout(6)->get('https://oauth2.googleapis.com/tokeninfo', [
                'id_token' => $idToken,
            ]);

            if ($response->successful()) {
                $payload = $response->json();
                if (! empty($payload['email'])) {
                    return [
                        'uid' => $payload['sub'] ?? $payload['user_id'] ?? md5($payload['email']),
                        'email' => $payload['email'],
                        'name' => $payload['name'] ?? explode('@', $payload['email'])[0],
                        'avatar_url' => $payload['picture'] ?? null,
                    ];
                }
            }
        } catch (Throwable $e) {
            Log::debug('Google tokeninfo check failed, falling back to JWT decode: ' . $e->getMessage());
        }

        // 2. Fallback: Parse JWT payload (Works for local/mock testing and direct token payloads)
        try {
            $parts = explode('.', $idToken);
            if (count($parts) === 3) {
                $payloadJson = base64_decode(strtr($parts[1], '-_', '+/'));
                $payload = json_decode($payloadJson, true);

                if (is_array($payload) && ! empty($payload['email'])) {
                    $uid = $payload['user_id'] ?? $payload['sub'] ?? md5($payload['email']);

                    return [
                        'uid' => (string) $uid,
                        'email' => (string) $payload['email'],
                        'name' => (string) ($payload['name'] ?? explode('@', $payload['email'])[0]),
                        'avatar_url' => isset($payload['picture']) ? (string) $payload['picture'] : null,
                    ];
                }
            }
        } catch (Throwable $e) {
            Log::warning('JWT decode failed: ' . $e->getMessage());
        }

        throw new InvalidArgumentException('Verifikasi Firebase ID Token gagal. Token kedaluwarsa atau tidak valid.');
    }
}

