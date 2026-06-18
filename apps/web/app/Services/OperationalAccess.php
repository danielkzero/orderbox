<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class OperationalAccess
{
    public function authorize(User $user, string $resource, string $ability, ?Model $model = null): void
    {
        if ($user->role === 'Admin') {
            return;
        }

        if ($user->role === 'Manager') {
            return;
        }

        abort_unless($user->role === 'SalesRepresentative', 403);

        $allowed = match ($resource) {
            'products', 'price-tables' => $ability === 'view',
            'customers' => in_array($ability, ['view', 'create', 'update'], true),
            'orders' => in_array($ability, ['view', 'create', 'update', 'delete'], true),
            default => false,
        };

        abort_unless($allowed, 403);

        if ($model instanceof Customer) {
            abort_unless($model->representatives()
                ->where('sales_representative_id', $this->representativeId($user))
                ->exists(), 404);
        }

        if ($model instanceof Order) {
            abort_unless($model->sales_representative_id === $this->representativeId($user), 404);
        }
    }

    public function scopeCustomers(Builder $query, User $user): Builder
    {
        if ($user->role !== 'SalesRepresentative') {
            return $query;
        }

        return $query->whereHas('representatives', fn (Builder $relation) => $relation
            ->where('sales_representative_id', $this->representativeId($user)));
    }

    public function scopeOrders(Builder $query, User $user): Builder
    {
        if ($user->role !== 'SalesRepresentative') {
            return $query;
        }

        return $query->where('sales_representative_id', $this->representativeId($user));
    }

    public function representativeId(User $user): int
    {
        $representativeId = $user->salesRepresentative()
            ->where('company_id', $user->company_id)
            ->where('active', true)
            ->value('id');

        abort_unless($representativeId, 403, 'O usuário não possui representante comercial ativo.');

        return (int) $representativeId;
    }
}
