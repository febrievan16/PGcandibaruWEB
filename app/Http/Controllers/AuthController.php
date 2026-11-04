<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Display login page.
     */
    public function showLogin(): View
    {
        return view('auth.login');
    }

    /**
     * Authenticate user session.
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();
            if ($user && $user->isAdmin()) {
                return redirect()->intended(route('admin.dashboard'));
            }

            return redirect()->intended(route('account.orders'));
        }

        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => 'Kombinasi email dan kata sandi tidak sesuai.']);
    }

    /**
     * Display optional user registration page (DEC-004).
     */
    public function showRegister(): View
    {
        return view('auth.register');
    }

    /**
     * Register a new visitor account (DEC-004, M5).
     */
    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:150', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $user = User::create([
            'supabase_uid' => 'local-' . Str::uuid(),
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'role' => 'user',
            'api_token' => 'gt-usr-' . Str::random(32),
            'password' => Hash::make($validated['password']),
        ]);

        // Link any prior guest orders with the same email to this new account
        Order::whereNull('user_id')
            ->where('buyer_email', $user->email)
            ->update(['user_id' => $user->id]);

        Auth::login($user);

        return redirect()->route('account.orders')
            ->with('status', 'Akun berhasil dibuat. Riwayat pemesanan Anda kini tersimpan otomatis.');
    }

    /**
     * Log out current user.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    /**
     * Display registered user's order history and E-Ticket downloads (M5: FR-10).
     */
    public function myOrders(): View
    {
        /** @var User $user */
        $user = Auth::user();

        $orders = Order::with(['schedule.event', 'tickets'])
            ->where('user_id', $user->id)
            ->latest('created_at')
            ->get();

        return view('account.orders', [
            'user' => $user,
            'orders' => $orders,
        ]);
    }
}

