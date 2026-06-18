<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Arr;

class AuditService
{
    public function record(User $actor, string $action, object $entity, ?array $oldValues = null, ?array $newValues = null): void
    {
        AuditLog::query()->create([
            'company_id' => $actor->company_id,
            'user_id' => $actor->id,
            'action' => $action,
            'entity_type' => class_basename($entity),
            'entity_id' => $entity->getKey(),
            'old_values' => $this->sanitize($oldValues),
            'new_values' => $this->sanitize($newValues),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    private function sanitize(?array $values): ?array
    {
        if ($values === null) {
            return null;
        }

        return Arr::except($values, [
            'password',
            'remember_token',
            'two_factor_secret',
            'secret_hash',
        ]);
    }
}
