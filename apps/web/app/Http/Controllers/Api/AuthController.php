<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Authentication\AuthenticationChallengeService;
use App\Services\Authentication\AuthenticationSessionService;
use App\Services\Authentication\CredentialAuthenticator;
use App\Services\Authentication\TwoFactorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(
        Request $request,
        CredentialAuthenticator $credentials,
        AuthenticationChallengeService $challenges,
        AuthenticationSessionService $sessions,
    ): JsonResponse {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = $credentials->attempt($validated['email'], $validated['password']);

        if (! $user) {
            throw ValidationException::withMessages(['email' => __('auth.failed')]);
        }

        if ($user->two_factor_enabled) {
            $challenge = $challenges->create($user, 'Mobile');

            return response()->json([
                'success' => true,
                'data' => [
                    'two_factor_required' => true,
                    'challenge_id' => $challenge->id,
                    'expires_at' => $challenge->expires_at,
                ],
            ], 202);
        }

        return $this->tokenResponse($sessions->activateMobile($user, $request->ip(), $request->userAgent())->plainTextToken);
    }

    public function confirmTwoFactor(
        Request $request,
        AuthenticationChallengeService $challenges,
        AuthenticationSessionService $sessions,
        TwoFactorService $twoFactor,
    ): JsonResponse {
        $validated = $request->validate([
            'challenge_id' => ['required', 'uuid'],
            'code' => ['required', 'string', 'size:6'],
        ]);

        $challenge = $challenges->valid($validated['challenge_id'], 'Mobile');

        if (! $twoFactor->verify($challenge->user, $validated['code'])) {
            throw ValidationException::withMessages(['code' => __('The authentication code is invalid.')]);
        }

        $token = $sessions->activateMobile($challenge->user, $request->ip(), $request->userAgent());
        $challenges->consume($challenge);

        return $this->tokenResponse($token->plainTextToken);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $request->user()->load('company'),
        ]);
    }

    public function logout(Request $request, AuthenticationSessionService $sessions): JsonResponse
    {
        $sessions->revokeMobile($request->user(), $request->user()->currentAccessToken()->getKey());

        return response()->json(['success' => true, 'data' => null]);
    }

    private function tokenResponse(string $plainTextToken): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'two_factor_required' => false,
                'token_type' => 'Bearer',
                'access_token' => $plainTextToken,
            ],
        ]);
    }
}
