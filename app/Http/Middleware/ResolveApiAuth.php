<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ResolveApiAuth
{
    /**
     * Handle an incoming request, resolving optional or required Bearer token / session user.
     */
    public function handle(Request $request, Closure $next, string $mode = 'optional'): Response
    {
        $bearer = $request->bearerToken();

        if ($bearer !== null && $bearer !== '') {
            $user = $this->resolveUserFromToken($bearer);

            if (! $user) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'UNAUTHORIZED',
                        'message' => 'Token autentikasi tidak valid atau telah kedaluwarsa.',
                        'details' => (object) [],
                    ],
                ], 401);
            }

            Auth::setUser($user);
            $request->setUserResolver(fn (): User => $user);
        }

        if ($mode === 'required' && ! Auth::check()) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'UNAUTHORIZED',
                    'message' => 'Autentikasi diperlukan untuk mengakses endpoint ini.',
                    'details' => (object) [],
                ],
            ], 401);
        }

        return $next($request);
    }

    /**
     * Resolve a User from an API token, Supabase UID, or JWT payload.
     */
    protected function resolveUserFromToken(string $token): ?User
    {
        $user = User::where('api_token', $token)
            ->orWhere('supabase_uid', $token)
            ->first();

        if ($user) {
            return $user;
        }

        // Support JWT structure (`header.payload.signature`) if passed by Supabase client
        $parts = explode('.', $token);
        if (count($parts) === 3) {
            $payloadJson = base64_decode(strtr($parts[1], '-_', '+/'), true);
            if (is_string($payloadJson)) {
                $claims = json_decode($payloadJson, true);
                if (is_array($claims) && ! empty($claims['sub'])) {
                    return User::where('supabase_uid', (string) $claims['sub'])
                        ->orWhere('email', (string) ($claims['email'] ?? ''))
                        ->first();
                }
            }
        }

        return null;
    }
}

