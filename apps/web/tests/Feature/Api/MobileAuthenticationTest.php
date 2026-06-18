<?php

namespace Tests\Feature\Api;

use App\Models\ApiClient;
use App\Models\AuthenticationSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

class MobileAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_mobile_login_requires_allowed_api_client(): void
    {
        $user = User::factory()->create();

        $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertForbidden()
            ->assertJsonPath('error.code', 'api_client_not_allowed');
    }

    public function test_mobile_login_is_rate_limited_by_api_client(): void
    {
        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this->withHeaders([
                'X-OrderBox-Client-Key' => 'blocked-client',
                'X-OrderBox-Client-Secret' => 'invalid-secret',
            ])->postJson('/api/v1/auth/login', [
                'email' => 'missing@example.test',
                'password' => 'invalid',
            ])->assertForbidden();
        }

        $this->withHeaders([
            'X-OrderBox-Client-Key' => 'blocked-client',
            'X-OrderBox-Client-Secret' => 'invalid-secret',
        ])->postJson('/api/v1/auth/login', [
            'email' => 'missing@example.test',
            'password' => 'invalid',
        ])->assertTooManyRequests()
            ->assertJsonPath('error.code', 'too_many_requests');
    }

    public function test_new_mobile_login_revokes_previous_mobile_token(): void
    {
        $user = User::factory()->create();

        $firstToken = $this->withHeaders($this->apiHeaders($user))->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertOk()->json('data.access_token');

        $secondToken = $this->withHeaders($this->apiHeaders($user))->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertOk()->json('data.access_token');

        $this->withToken($firstToken)->getJson('/api/v1/auth/me')->assertUnauthorized();
        $this->withToken($secondToken)->getJson('/api/v1/auth/me')->assertOk();
        $this->assertSame(1, AuthenticationSession::query()
            ->where('user_id', $user->id)
            ->where('channel', 'Mobile')
            ->where('active_slot', true)
            ->count());
    }

    public function test_web_and_mobile_sessions_can_coexist(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('dashboard', absolute: false));

        $this->withHeaders($this->apiHeaders($user))->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertOk();

        $this->assertSame(2, AuthenticationSession::query()
            ->where('user_id', $user->id)
            ->where('active_slot', true)
            ->count());
    }

    public function test_two_factor_challenge_preserves_previous_mobile_session_until_confirmation(): void
    {
        $google2fa = app(Google2FA::class);
        $secret = $google2fa->generateSecretKey();
        $user = User::factory()->create();

        $previousToken = $this->withHeaders($this->apiHeaders($user))->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertOk()->json('data.access_token');

        $user->update([
            'two_factor_enabled' => true,
            'two_factor_secret' => $secret,
        ]);

        $challengeId = $this->withHeaders($this->apiHeaders($user))->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertStatus(202)->json('data.challenge_id');

        $this->withToken($previousToken)->getJson('/api/v1/auth/me')->assertOk();

        $newToken = $this->withHeaders($this->apiHeaders($user))->postJson('/api/v1/auth/2fa/confirm', [
            'challenge_id' => $challengeId,
            'code' => $google2fa->getCurrentOtp($secret),
        ])->assertOk()->json('data.access_token');

        $this->assertNotSame($previousToken, $newToken);
        $this->assertDatabaseHas('authentication_sessions', [
            'user_id' => $user->id,
            'channel' => 'Mobile',
            'personal_access_token_id' => explode('|', $newToken, 2)[0],
            'active_slot' => true,
        ]);

        $this->flushHeaders();
        $this->withToken($previousToken)
            ->getJson('/api/v1/auth/me')
            ->assertUnauthorized();
        $this->flushHeaders();
        app('auth')->forgetGuards();
        $this->withToken($newToken)
            ->getJson('/api/v1/auth/me')
            ->assertOk();
    }

    private function apiHeaders(User $user): array
    {
        ApiClient::query()->updateOrCreate(
            ['client_key' => 'test-mobile-client'],
            [
                'company_id' => $user->company_id,
                'name' => 'Test mobile client',
                'channel' => 'Mobile',
                'secret_hash' => Hash::make('test-secret'),
                'active' => true,
            ],
        );

        return [
            'X-OrderBox-Client-Key' => 'test-mobile-client',
            'X-OrderBox-Client-Secret' => 'test-secret',
        ];
    }
}
