<?php

namespace App\Http\Controllers;

use App\Services\FirebaseAuthService;
use App\Services\SocialAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class SocialAuthController extends Controller
{
    /**
     * Redirect visitor to social / SSO provider OAuth screen.
     */
    public function redirect(string $provider, SocialAuthService $service): Response
    {
        return $service->getRedirectResponse($provider);
    }

    /**
     * Handle incoming OAuth callback from social / SSO provider.
     */
    public function callback(string $provider, Request $request, SocialAuthService $service): RedirectResponse
    {
        // Handle cancelled or denied consent
        if ($request->has('error')) {
            $errorDesc = $request->input('error_description') ?? $request->input('error');

            return redirect()->route('login')->withErrors([
                'email' => "Proses autentikasi dengan {$provider} dibatalkan: {$errorDesc}",
            ]);
        }

        try {
            $user = $service->handleCallback($provider);

            Auth::login($user, true);
            $request->session()->regenerate();

            if ($user->isAdmin()) {
                return redirect()->intended(route('admin.dashboard'));
            }

            return redirect()->intended(route('account.orders'))
                ->with('status', 'Berhasil masuk menggunakan akun Google.');
        } catch (Throwable $e) {
            return redirect()->route('login')->withErrors([
                'email' => 'Gagal menyelesaikan masuk dengan Google: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Authenticate or register user using Firebase ID token (Google SSO).
     */
    public function firebaseSession(
        Request $request,
        FirebaseAuthService $firebaseAuthService,
        SocialAuthService $socialAuthService
    ): JsonResponse {
        $validated = $request->validate([
            'id_token' => ['required', 'string'],
        ]);

        try {
            $claims = $firebaseAuthService->verifyIdToken($validated['id_token']);
            $user = $socialAuthService->handleFirebaseClaims($claims, 'firebase_google');

            Auth::login($user, true);
            $request->session()->regenerate();

            $redirectUrl = $user->isAdmin()
                ? route('admin.dashboard')
                : route('account.orders');

            return response()->json([
                'success' => true,
                'message' => 'Berhasil masuk menggunakan akun Google (Firebase).',
                'user' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'avatar_url' => $user->avatar_url,
                ],
                'redirect' => $redirectUrl,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Autentikasi Firebase gagal: ' . $e->getMessage(),
            ], 422);
        }
    }
}
