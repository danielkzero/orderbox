<?php

namespace App\Services\Authentication;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CredentialAuthenticator
{
    public function attempt(string $email, string $password): ?User
    {
        $user = User::query()
            ->with('company')
            ->where('email', $email)
            ->first();

        if (
            ! $user
            || ! Hash::check($password, $user->password)
            || ! $user->active
            || ! $user->company?->active
        ) {
            return null;
        }

        return $user;
    }
}
