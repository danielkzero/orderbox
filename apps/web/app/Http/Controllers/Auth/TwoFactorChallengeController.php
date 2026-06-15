<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\Authentication\AuthenticationChallengeService;
use App\Services\Authentication\AuthenticationSessionService;
use App\Services\Authentication\TwoFactorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TwoFactorChallengeController extends Controller
{
    public function show(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has('authentication_challenge_id')) {
            return redirect()->route('login');
        }

        return view('auth.two-factor-challenge');
    }

    public function store(
        Request $request,
        AuthenticationChallengeService $challenges,
        AuthenticationSessionService $sessions,
        TwoFactorService $twoFactor,
    ): RedirectResponse {
        $request->validate(['code' => ['required', 'string', 'size:6']]);

        $challenge = $challenges->valid(
            (string) $request->session()->get('authentication_challenge_id'),
            'Web',
        );

        if (! $twoFactor->verify($challenge->user, $request->string('code')->toString())) {
            throw ValidationException::withMessages(['code' => __('The authentication code is invalid.')]);
        }

        Auth::guard('web')->login($challenge->user, $challenge->remember);
        $request->session()->regenerate();
        $sessionKey = (string) Str::uuid();
        $request->session()->put('authentication_session_key', $sessionKey);
        $sessions->activateWeb(
            $challenge->user,
            $request->session()->getId(),
            $sessionKey,
            $request->ip(),
            $request->userAgent(),
        );
        $challenges->consume($challenge);
        $request->session()->forget('authentication_challenge_id');

        return redirect()->intended(route('dashboard', absolute: false));
    }
}
