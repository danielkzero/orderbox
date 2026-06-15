<?php

namespace App\Services\Authentication;

use App\Models\User;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorService
{
    public function __construct(private readonly Google2FA $google2fa) {}

    public function verify(User $user, string $code): bool
    {
        return $user->two_factor_enabled
            && filled($user->two_factor_secret)
            && $this->google2fa->verifyKey($user->two_factor_secret, $code);
    }
}
