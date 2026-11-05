<?php

namespace Tests\Feature;

use App\Models\ConnectedAccount;
use App\Models\Order;
use App\Models\Schedule;
use App\Models\User;
use App\Services\FirebaseAuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FirebaseAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->seed();
    }

    /**
     * Helper to create a base64url-encoded test JWT ID token.
     */
    protected function createMockIdToken(string $sub, string $email, string $name = 'Google User'): string
    {
        $header = rtrim(strtr(base64_encode(json_encode(['alg' => 'none', 'typ' => 'JWT'])), '+/', '-_'), '=');
        $payload = rtrim(strtr(base64_encode(json_encode([
            'sub' => $sub,
            'email' => $email,
            'name' => $name,
            'picture' => 'https://lh3.googleusercontent.com/test-avatar.jpg',
        ])), '+/', '-_'), '=');

        return "{$header}.{$payload}.mocksignature";
    }

    public function test_firebase_auth_service_can_parse_valid_id_token(): void
    {
        $service = new FirebaseAuthService();
        $token = $this->createMockIdToken('google-uid-1001', 'wisatawan.firebase@gmail.com', 'Wisatawan Firebase');

        $claims = $service->verifyIdToken($token);

        $this->assertSame('google-uid-1001', $claims['uid']);
        $this->assertSame('wisatawan.firebase@gmail.com', $claims['email']);
        $this->assertSame('Wisatawan Firebase', $claims['name']);
        $this->assertSame('https://lh3.googleusercontent.com/test-avatar.jpg', $claims['avatar_url']);
    }

    public function test_firebase_session_endpoint_registers_and_logs_in_new_user(): void
    {
        $token = $this->createMockIdToken('google-uid-2002', 'newuser.google@gmail.com', 'New Google Visitor');

        $response = $this->postJson(route('auth.firebase.session'), [
            'id_token' => $token,
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'redirect' => route('account.orders'),
            ]);

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'newuser.google@gmail.com',
            'name' => 'New Google Visitor',
            'role' => 'user',
        ]);

        $user = User::where('email', 'newuser.google@gmail.com')->first();
        $this->assertNotNull($user);
        $this->assertDatabaseHas('connected_accounts', [
            'user_id' => $user->id,
            'provider' => 'firebase_google',
            'provider_user_id' => 'google-uid-2002',
        ]);
    }

    public function test_firebase_session_links_existing_user_and_preserves_role(): void
    {
        $existingAdmin = User::where('email', 'admin@pgcandibaru.co.id')->firstOrFail();

        $token = $this->createMockIdToken('google-admin-999', 'admin@pgcandibaru.co.id', 'Existing Admin');

        $response = $this->postJson(route('auth.firebase.session'), [
            'id_token' => $token,
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'redirect' => route('admin.dashboard'),
            ]);

        $this->assertAuthenticatedAs($existingAdmin);
        $this->assertDatabaseHas('connected_accounts', [
            'user_id' => $existingAdmin->id,
            'provider' => 'firebase_google',
            'provider_user_id' => 'google-admin-999',
        ]);
    }

    public function test_firebase_session_auto_links_prior_guest_orders(): void
    {
        $guestEmail = 'guest.booked@gmail.com';
        $schedule = Schedule::firstOrFail();

        $order = Order::create([
            'order_code' => 'GT-GUEST-TEST01',
            'idempotency_key' => 'idem-guest-001',
            'access_token' => 'guest-access-token',
            'schedule_id' => $schedule->id,
            'user_id' => null,
            'buyer_name' => 'Tamu Guest',
            'buyer_email' => $guestEmail,
            'buyer_phone' => '08123456789',
            'ticket_qty' => 2,
            'unit_price' => 50000,
            'total_amount' => 100000,
            'status' => 'paid',
            'expires_at' => now()->addDay(),
        ]);

        $token = $this->createMockIdToken('google-guest-777', $guestEmail, 'Guest Booked');

        $response = $this->postJson(route('auth.firebase.session'), [
            'id_token' => $token,
        ]);

        $response->assertOk();

        $user = User::where('email', $guestEmail)->first();
        $this->assertNotNull($user);
        $this->assertSame($user->id, $order->fresh()->user_id);
    }

    public function test_firebase_session_fails_when_id_token_is_missing(): void
    {
        $response = $this->postJson(route('auth.firebase.session'), []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['id_token']);
    }
}
