<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\MobileLoginRequest;
use App\Http\Requests\Api\MobileTwoFactorRequest;
use App\Http\Resources\AuthenticatedUserResource;
use App\Services\AuditService;
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
        MobileLoginRequest $request,
        CredentialAuthenticator $credentials,
        AuthenticationChallengeService $challenges,
        AuthenticationSessionService $sessions,
        AuditService $audit,
    ): JsonResponse {
        $validated = $request->validated();

        $user = $credentials->attempt($validated['email'], $validated['password']);

        if (! $user) {
            throw ValidationException::withMessages(['email' => __('auth.failed')]);
        }

        $apiClient = $request->attributes->get('api_client');

        if ($apiClient && $apiClient->company_id !== $user->company_id) {
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

        $token = $sessions->activateMobile($user, $request->ip(), $request->userAgent());
        $audit->record($user, 'Login', $user, null, ['channel' => 'Mobile']);

        return $this->tokenResponse($token->plainTextToken);
    }

    public function confirmTwoFactor(
        MobileTwoFactorRequest $request,
        AuthenticationChallengeService $challenges,
        AuthenticationSessionService $sessions,
        TwoFactorService $twoFactor,
        AuditService $audit,
    ): JsonResponse {
        $validated = $request->validated();

        $challenge = $challenges->valid($validated['challenge_id'], 'Mobile');
        $apiClient = $request->attributes->get('api_client');

        if ($apiClient && $apiClient->company_id !== $challenge->company_id) {
            throw ValidationException::withMessages(['challenge_id' => __('The authentication challenge is invalid.')]);
        }

        if (! $twoFactor->verify($challenge->user, $validated['code'])) {
            throw ValidationException::withMessages(['code' => __('The authentication code is invalid.')]);
        }

        $token = $sessions->activateMobile($challenge->user, $request->ip(), $request->userAgent());
        $challenges->consume($challenge);
        $audit->record($challenge->user, 'Login', $challenge->user, null, ['channel' => 'Mobile', 'two_factor' => true]);

        return $this->tokenResponse($token->plainTextToken);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => new AuthenticatedUserResource($request->user()->load('company')),
        ]);
    }

    public function logout(Request $request, AuthenticationSessionService $sessions, AuditService $audit): JsonResponse
    {
        $audit->record($request->user(), 'Logout', $request->user(), ['channel' => 'Mobile'], null);
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
