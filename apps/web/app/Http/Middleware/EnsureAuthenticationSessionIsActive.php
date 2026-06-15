<?php

namespace App\Http\Middleware;

use App\Models\AuthenticationSession;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAuthenticationSessionIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $token = $user?->currentAccessToken();
        $channel = $token ? 'Mobile' : 'Web';
        $column = $token ? 'personal_access_token_id' : 'session_key_hash';
        $identifier = $token
            ? $token->getKey()
            : $this->webSessionKeyHash($request);

        $session = $user
            ? AuthenticationSession::query()
                ->where('user_id', $user->id)
                ->where('channel', $channel)
                ->where($column, $identifier)
                ->where('active_slot', true)
                ->first()
            : null;

        if (! $session || ! $user->active || ! $user->company()->where('active', true)->exists()) {
            if ($token) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'authentication_session_revoked',
                        'message' => 'Authentication session is no longer active.',
                    ],
                ], 401);
            }

            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login');
        }

        $session->forceFill(['last_activity_at' => now()])->save();

        return $next($request);
    }

    private function webSessionKeyHash(Request $request): ?string
    {
        $sessionKey = $request->session()->get('authentication_session_key');

        return $sessionKey ? hash('sha256', $sessionKey) : null;
    }
}
