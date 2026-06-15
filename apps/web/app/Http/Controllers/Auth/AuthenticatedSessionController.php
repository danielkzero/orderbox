<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\Authentication\AuthenticationChallengeService;
use App\Services\Authentication\AuthenticationSessionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(
        LoginRequest $request,
        AuthenticationChallengeService $challenges,
        AuthenticationSessionService $sessions,
    ): RedirectResponse {
        $user = $request->authenticateUser();

        if ($user->two_factor_enabled) {
            $challenge = $challenges->create($user, 'Web', $request->boolean('remember'));
            $request->session()->put('authentication_challenge_id', $challenge->id);

            return redirect()->route('auth.2fa.show');
        }

        Auth::guard('web')->login($user, $request->boolean('remember'));
        $request->session()->regenerate();
        $sessionKey = (string) Str::uuid();
        $request->session()->put('authentication_session_key', $sessionKey);
        $sessions->activateWeb(
            $user,
            $request->session()->getId(),
            $sessionKey,
            $request->ip(),
            $request->userAgent(),
        );

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request, AuthenticationSessionService $sessions): RedirectResponse
    {
        $sessions->revokeWeb($request->user());
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
