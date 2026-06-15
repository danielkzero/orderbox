<?php

namespace App\Services\Authentication;

use App\Models\AuthenticationChallenge;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class AuthenticationChallengeService
{
    public function create(User $user, string $channel, bool $remember = false): AuthenticationChallenge
    {
        AuthenticationChallenge::query()
            ->where('user_id', $user->id)
            ->where('channel', $channel)
            ->whereNull('consumed_at')
            ->delete();

        return AuthenticationChallenge::query()->create([
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'channel' => $channel,
            'remember' => $remember,
            'expires_at' => now()->addMinutes(5),
        ]);
    }

    public function valid(string $id, string $channel): AuthenticationChallenge
    {
        $challenge = AuthenticationChallenge::query()
            ->with('user.company')
            ->whereKey($id)
            ->where('channel', $channel)
            ->whereNull('consumed_at')
            ->where('expires_at', '>', now())
            ->first();

        if (! $challenge || ! $challenge->user->active || ! $challenge->user->company->active) {
            throw ValidationException::withMessages([
                'code' => __('The authentication challenge is invalid or expired.'),
            ]);
        }

        return $challenge;
    }

    public function consume(AuthenticationChallenge $challenge): void
    {
        $challenge->forceFill(['consumed_at' => now()])->save();
    }
}
