<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminRole
{
    /**
     * Ensure the current user is authenticated and has the `admin` role.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (! $user) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'UNAUTHORIZED',
                        'message' => 'Silakan masuk terlebih dahulu.',
                        'details' => (object) [],
                    ],
                ], 401);
            }

            return redirect()->route('login');
        }

        if (! $user->isAdmin()) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'FORBIDDEN',
                        'message' => 'Akses ditolak. Endpoint ini memerlukan peran admin.',
                        'details' => (object) [],
                    ],
                ], 403);
            }

            abort(403, 'Akses khusus Administrator PT Pabrik Gula Candi Baru.');
        }

        return $next($request);
    }
}

