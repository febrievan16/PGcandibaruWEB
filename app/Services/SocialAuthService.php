<?php

namespace App\Services;

use App\Models\ConnectedAccount;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\Response;

class SocialAuthService
{
    /**
     * Supported OAuth / SSO providers.
     *
     * @var list<string>
     */
    protected const SUPPORTED_PROVIDERS = ['google'];

    /**
     * Check if provider is supported.
     */
    public function isSupported(string $provider): bool
    {
        return in_array(strtolower($provider), self::SUPPORTED_PROVIDERS, true);
    }

    /**
     * Get OAuth redirect response to provider consent screen.
     */
    public function getRedirectResponse(string $provider): Response
    {
        $provider = strtolower($provider);
        if (! $this->isSupported($provider)) {
            throw new InvalidArgumentException("Provider autentikasi [{$provider}] belum didukung.");
        }

        return Socialite::driver($provider)->redirect();
    }

    /**
     * Process OAuth callback, retrieve or create user and connected account record.
     */
    public function handleCallback(string $provider, ?SocialiteUser $socialUser = null): User
    {
        $provider = strtolower($provider);
        if (! $this->isSupported($provider)) {
            throw new InvalidArgumentException("Provider autentikasi [{$provider}] belum didukung.");
        }

        if ($socialUser === null) {
            $socialUser = Socialite::driver($provider)->user();
        }

        $email = $socialUser->getEmail();
        if (empty($email)) {
            throw new InvalidArgumentException('Akun sosial tidak menyediakan alamat email yang valid.');
        }

        $providerUserId = (string) $socialUser->getId();
        $avatarUrl = $socialUser->getAvatar();
        $name = $socialUser->getName() ?: explode('@', $email)[0];

        return DB::transaction(function () use ($provider, $providerUserId, $email, $avatarUrl, $name): User {
            // 1. Check if connected account mapping exists
            $connectedAccount = ConnectedAccount::with('user')
                ->where('provider', $provider)
                ->where('provider_user_id', $providerUserId)
                ->first();

            if ($connectedAccount) {
                $user = $connectedAccount->user;

                // Update avatar if changed
                if ($connectedAccount->avatar_url !== $avatarUrl) {
                    $connectedAccount->update(['avatar_url' => $avatarUrl]);
                }

                // Auto-verify email if not yet set
                if (! $user->email_verified_at) {
                    $user->update(['email_verified_at' => now()]);
                }

                return $user;
            }

            // 2. Account not connected yet: find user by verified email (Auto-linking)
            $user = User::where('email', $email)->first();

            if ($user) {
                // Link Google to existing user
                ConnectedAccount::create([
                    'user_id' => $user->id,
                    'provider' => $provider,
                    'provider_user_id' => $providerUserId,
                    'avatar_url' => $avatarUrl,
                ]);

                if (! $user->email_verified_at) {
                    $user->update(['email_verified_at' => now()]);
                }

                return $user;
            }

            // 3. User does not exist: create new visitor account
            $user = User::create([
                'supabase_uid' => 'social-' . Str::uuid(),
                'name' => $name,
                'email' => $email,
                'phone' => null,
                'role' => 'user', // strictly visitor role
                'api_token' => 'gt-usr-' . Str::random(32),
                'email_verified_at' => now(),
                'password' => null,
            ]);

            ConnectedAccount::create([
                'user_id' => $user->id,
                'provider' => $provider,
                'provider_user_id' => $providerUserId,
                'avatar_url' => $avatarUrl,
            ]);

            // Link any prior guest orders with this email
            Order::whereNull('user_id')
                ->where('buyer_email', $user->email)
                ->update(['user_id' => $user->id]);

            return $user;
        });
    }

    /**
     * Process authentication from verified Firebase user claims.
     *
     * @param array{uid: string, email: string, name: ?string, avatar_url: ?string} $claims
     */
    public function handleFirebaseClaims(array $claims, string $provider = 'firebase_google'): User
    {
        $email = $claims['email'];
        $providerUserId = (string) $claims['uid'];
        $avatarUrl = $claims['avatar_url'] ?? null;
        $name = $claims['name'] ?: explode('@', $email)[0];

        return DB::transaction(function () use ($provider, $providerUserId, $email, $avatarUrl, $name): User {
            // 1. Check if connected account mapping exists
            $connectedAccount = ConnectedAccount::with('user')
                ->where('provider', $provider)
                ->where('provider_user_id', $providerUserId)
                ->first();

            if ($connectedAccount) {
                $user = $connectedAccount->user;

                if ($connectedAccount->avatar_url !== $avatarUrl && ! empty($avatarUrl)) {
                    $connectedAccount->update(['avatar_url' => $avatarUrl]);
                }

                if (! $user->email_verified_at) {
                    $user->update(['email_verified_at' => now()]);
                }

                return $user;
            }

            // 2. Account not connected yet: find user by verified email (Auto-linking)
            $user = User::where('email', $email)->first();

            if ($user) {
                ConnectedAccount::create([
                    'user_id' => $user->id,
                    'provider' => $provider,
                    'provider_user_id' => $providerUserId,
                    'avatar_url' => $avatarUrl,
                ]);

                if (! $user->email_verified_at) {
                    $user->update(['email_verified_at' => now()]);
                }

                return $user;
            }

            // 3. User does not exist: create new visitor account
            $user = User::create([
                'supabase_uid' => 'firebase-' . Str::uuid(),
                'name' => $name,
                'email' => $email,
                'phone' => null,
                'role' => 'user', // strictly visitor role
                'api_token' => 'gt-usr-' . Str::random(32),
                'email_verified_at' => now(),
                'password' => null,
            ]);

            ConnectedAccount::create([
                'user_id' => $user->id,
                'provider' => $provider,
                'provider_user_id' => $providerUserId,
                'avatar_url' => $avatarUrl,
            ]);

            // Link any prior guest orders with this email
            Order::whereNull('user_id')
                ->where('buyer_email', $user->email)
                ->update(['user_id' => $user->id]);

            return $user;
        });
    }
}

