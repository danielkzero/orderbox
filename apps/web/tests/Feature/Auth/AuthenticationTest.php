<?php

namespace Tests\Feature\Auth;

use App\Models\AuthenticationSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
        $this->get('/dashboard')->assertOk();
        $this->assertDatabaseHas('authentication_sessions', [
            'user_id' => $user->id,
            'channel' => 'Web',
            'active_slot' => true,
        ]);
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response = $this->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
        $this->assertDatabaseMissing('authentication_sessions', [
            'user_id' => $user->id,
            'channel' => 'Web',
            'active_slot' => true,
        ]);
    }

    public function test_two_factor_confirmation_revokes_previous_web_session_only_after_confirmation(): void
    {
        $google2fa = app(Google2FA::class);
        $secret = $google2fa->generateSecretKey();
        $user = User::factory()->create([
            'two_factor_enabled' => true,
            'two_factor_secret' => $secret,
        ]);

        $previous = AuthenticationSession::query()->create([
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'channel' => 'Web',
            'active_slot' => true,
            'session_key_hash' => hash('sha256', '49b6e654-7ab8-4c92-b588-a1a02c7ef868'),
            'web_session_id' => 'previous-web-session',
            'last_activity_at' => now(),
        ]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('auth.2fa.show'));

        $this->assertGuest();
        $this->assertTrue((bool) $previous->fresh()->active_slot);

        $this->post('/two-factor-challenge', [
            'code' => $google2fa->getCurrentOtp($secret),
        ])->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticatedAs($user);
        $this->assertNull($previous->fresh()->active_slot);
        $this->assertSame(1, AuthenticationSession::query()
            ->where('user_id', $user->id)
            ->where('channel', 'Web')
            ->where('active_slot', true)
            ->count());
    }
}
