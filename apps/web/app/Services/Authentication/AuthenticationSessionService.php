<?php

namespace App\Services\Authentication;

use App\Models\AuthenticationSession;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\NewAccessToken;

class AuthenticationSessionService
{
    public function activateWeb(
        User $user,
        string $sessionId,
        string $sessionKey,
        ?string $ip,
        ?string $userAgent,
    ): void {
        DB::transaction(function () use ($user, $sessionId, $sessionKey, $ip, $userAgent): void {
            $previous = $this->revokeActive($user, 'Web');

            if ($previous?->web_session_id && $previous->web_session_id !== $sessionId) {
                DB::table('sessions')->where('id', $previous->web_session_id)->delete();
            }

            AuthenticationSession::query()->create([
                'company_id' => $user->company_id,
                'user_id' => $user->id,
                'channel' => 'Web',
                'active_slot' => true,
                'session_key_hash' => hash('sha256', $sessionKey),
                'web_session_id' => $sessionId,
                'ip_address' => $ip,
                'user_agent' => $userAgent,
                'last_activity_at' => now(),
            ]);

            $user->forceFill(['last_login_at' => now()])->save();
        });
    }

    public function activateMobile(User $user, ?string $ip, ?string $userAgent): NewAccessToken
    {
        return DB::transaction(function () use ($user, $ip, $userAgent): NewAccessToken {
            $previous = $this->revokeActive($user, 'Mobile');

            if ($previous?->personal_access_token_id) {
                DB::table('personal_access_tokens')->where('id', $previous->personal_access_token_id)->delete();
            }

            $token = $user->createToken('mobile');

            AuthenticationSession::query()->create([
                'company_id' => $user->company_id,
                'user_id' => $user->id,
                'channel' => 'Mobile',
                'active_slot' => true,
                'personal_access_token_id' => $token->accessToken->id,
                'ip_address' => $ip,
                'user_agent' => $userAgent,
                'last_activity_at' => now(),
            ]);

            $user->forceFill(['last_login_at' => now()])->save();

            return $token;
        });
    }

    public function revokeWeb(User $user): void
    {
        DB::transaction(function () use ($user): void {
            $this->revokeActive($user, 'Web');
        });
    }

    public function revokeMobile(User $user, int|string $tokenId): void
    {
        DB::transaction(function () use ($user, $tokenId): void {
            $this->revokeMatching($user, 'Mobile', 'personal_access_token_id', $tokenId);
            DB::table('personal_access_tokens')->where('id', $tokenId)->delete();
        });
    }

    public function revokeAll(User $user): void
    {
        DB::transaction(function () use ($user): void {
            $sessions = AuthenticationSession::query()
                ->where('user_id', $user->id)
                ->where('active_slot', true)
                ->lockForUpdate()
                ->get();

            DB::table('sessions')
                ->whereIn('id', $sessions->pluck('web_session_id')->filter())
                ->delete();

            DB::table('personal_access_tokens')
                ->whereIn('id', $sessions->pluck('personal_access_token_id')->filter())
                ->delete();

            AuthenticationSession::query()
                ->whereIn('id', $sessions->pluck('id'))
                ->update([
                    'active_slot' => null,
                    'revoked_at' => now(),
                    'updated_at' => now(),
                ]);
        });
    }

    private function revokeActive(User $user, string $channel): ?AuthenticationSession
    {
        $session = AuthenticationSession::query()
            ->where('user_id', $user->id)
            ->where('channel', $channel)
            ->where('active_slot', true)
            ->lockForUpdate()
            ->first();

        if ($session) {
            $session->forceFill([
                'active_slot' => null,
                'revoked_at' => now(),
            ])->save();
        }

        return $session;
    }

    private function revokeMatching(User $user, string $channel, string $column, int|string $value): void
    {
        AuthenticationSession::query()
            ->where('user_id', $user->id)
            ->where('channel', $channel)
            ->where($column, $value)
            ->where('active_slot', true)
            ->update([
                'active_slot' => null,
                'revoked_at' => now(),
                'updated_at' => now(),
            ]);
    }
}
