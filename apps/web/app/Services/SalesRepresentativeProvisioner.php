<?php

namespace App\Services;

use App\Models\SalesRepresentative;
use App\Models\User;

class SalesRepresentativeProvisioner
{
    public function ensure(User $user, bool $activate = false): ?SalesRepresentative
    {
        if ($user->role !== 'SalesRepresentative') {
            return null;
        }

        $representative = SalesRepresentative::query()
            ->where('company_id', $user->company_id)
            ->where('user_id', $user->id)
            ->first();

        if ($representative) {
            if ($activate && $user->active && ! $representative->active) {
                $representative->update(['active' => true]);
            }

            return $representative;
        }

        return SalesRepresentative::query()->create([
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'code' => $this->availableCode($user),
            'active' => (bool) $user->active,
        ]);
    }

    public function deactivate(User $user): void
    {
        SalesRepresentative::query()
            ->where('company_id', $user->company_id)
            ->where('user_id', $user->id)
            ->update(['active' => false]);
    }

    private function availableCode(User $user): string
    {
        $base = 'REP-USR-'.$user->id;
        $code = $base;
        $suffix = 2;

        while (SalesRepresentative::query()
            ->where('company_id', $user->company_id)
            ->where('code', $code)
            ->exists()) {
            $code = $base.'-'.$suffix;
            $suffix++;
        }

        return $code;
    }
}
