<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\PriceTable;
use Illuminate\Support\Collection;

class ApplicablePriceTableService
{
    public function forCustomer(Customer $customer): Collection
    {
        $directTableIds = $customer->relationLoaded('priceTables')
            ? $customer->priceTables->pluck('id')
            : $customer->priceTables()->pluck('price_tables.id');
        $resolvedRegionId = $customer->region_id;

        return PriceTable::query()
            ->with('regions:id')
            ->where('company_id', $customer->company_id)
            ->where('active', true)
            ->where(function ($query) use ($directTableIds, $resolvedRegionId): void {
                $query->whereDoesntHave('regions');

                if ($resolvedRegionId) {
                    $query->orWhereHas('regions', fn ($relation) => $relation->whereKey($resolvedRegionId));
                }

                if ($directTableIds->isNotEmpty()) {
                    $query->orWhereIn('id', $directTableIds);
                }
            })
            ->get()
            ->sortByDesc(function (PriceTable $priceTable) use ($directTableIds, $resolvedRegionId): int {
                if ($directTableIds->contains($priceTable->id)) {
                    return 2;
                }

                return $resolvedRegionId && $priceTable->regions->contains('id', $resolvedRegionId) ? 1 : 0;
            })
            ->values();
    }

    public function forCustomers(Collection $customers): Collection
    {
        if ($customers->isEmpty()) {
            return collect();
        }

        $customers->loadMissing('priceTables');
        $tables = PriceTable::query()
            ->with('regions:id')
            ->where('company_id', $customers->first()->company_id)
            ->where('active', true)
            ->orderBy('name')
            ->get();

        return $customers->mapWithKeys(function (Customer $customer) use ($tables): array {
            $directTableIds = $customer->priceTables->pluck('id');
            $applicable = $tables
                ->filter(fn (PriceTable $table): bool => $table->regions->isEmpty()
                    || $table->regions->contains('id', $customer->region_id)
                    || $directTableIds->contains($table->id))
                ->sortByDesc(fn (PriceTable $table): int => $directTableIds->contains($table->id)
                    ? 2
                    : ($table->regions->contains('id', $customer->region_id) && $customer->region_id ? 1 : 0))
                ->values();

            return [$customer->id => $applicable];
        });
    }
}
