<?php

namespace App\Http\Controllers;

use App\Models\AuthenticationSession;
use App\Services\AuditService;
use App\Services\Authentication\AuthenticationSessionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use PragmaRX\Google2FA\Google2FA;

class SecurityController extends Controller
{
    public function index(Request $request, Google2FA $google2fa): View
    {
        $secret = $request->session()->get('two_factor_setup_secret');

        if (! $request->user()->two_factor_enabled && ! $secret) {
            $secret = $google2fa->generateSecretKey();
            $request->session()->put('two_factor_setup_secret', $secret);
        }

        return view('admin.security.index', [
            'secret' => $secret,
            'sessions' => AuthenticationSession::query()
                ->where('user_id', $request->user()->id)
                ->latest()
                ->limit(20)
                ->get(),
        ]);
    }

    public function enable(Request $request, Google2FA $google2fa, AuditService $audit): RedirectResponse
    {
        $data = $request->validate(['code' => ['required', 'string', 'size:6']]);
        $secret = $request->session()->get('two_factor_setup_secret');

        if (! $secret || ! $google2fa->verifyKey($secret, $data['code'])) {
            throw ValidationException::withMessages(['code' => 'Código 2FA inválido.']);
        }

        $request->user()->update(['two_factor_enabled' => true, 'two_factor_secret' => $secret]);
        $audit->record($request->user(), 'Enable2FA', $request->user(), ['two_factor_enabled' => false], ['two_factor_enabled' => true]);
        $request->session()->forget('two_factor_setup_secret');

        return back()->with('status', 'Autenticação em dois fatores ativada.');
    }

    public function disable(Request $request, AuditService $audit): RedirectResponse
    {
        $data = $request->validate(['password' => ['required', 'current_password']]);

        if (! Hash::check($data['password'], $request->user()->password)) {
            throw ValidationException::withMessages(['password' => 'Senha inválida.']);
        }

        $request->user()->update(['two_factor_enabled' => false, 'two_factor_secret' => null]);
        $audit->record($request->user(), 'Disable2FA', $request->user(), ['two_factor_enabled' => true], ['two_factor_enabled' => false]);

        return back()->with('status', 'Autenticação em dois fatores desativada.');
    }

    public function revoke(Request $request, AuthenticationSession $authenticationSession, AuthenticationSessionService $sessions, AuditService $audit): RedirectResponse
    {
        abort_if($authenticationSession->user_id !== $request->user()->id, 404);
        $sessions->revokeSession($authenticationSession);
        $audit->record($request->user(), 'RevokeSession', $authenticationSession, ['active' => true], ['active' => false]);

        return back()->with('status', 'Sessão revogada.');
    }
}
