<?php

namespace App\Models;

use App\Services\CommercialRegionResolver;
use App\Support\BrazilianDocument;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class Customer extends Model
{
    protected $guarded = [];

    public function setDocumentAttribute(?string $value): void
    {
        $this->attributes['document'] = BrazilianDocument::normalize($value);
    }

    protected function casts(): array
    {
        return ['active' => 'boolean', 'credit_limit' => 'decimal:2'];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(CustomerAddress::class);
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(CustomerContact::class);
    }

    public function representatives(): HasMany
    {
        return $this->hasMany(CustomerRepresentative::class);
    }

    public function priceTables(): BelongsToMany
    {
        return $this->belongsToMany(PriceTable::class)->withTimestamps();
    }

    public function applicablePriceTables(): Collection
    {
        $directTableIds = $this->priceTables()->pluck('price_tables.id');
        $defaultAddress = $this->defaultAddressRecord();
        $resolvedRegionId = null;

        if ($defaultAddress && filled($defaultAddress->state)) {
            $resolvedRegionId = app(CommercialRegionResolver::class)->resolve(
                $this->company_id,
                $defaultAddress->state,
                $defaultAddress->city,
                $defaultAddress->municipality_ibge_code,
            )?->id;
        }

        return PriceTable::query()
            ->where('company_id', $this->company_id)
            ->where('active', true)
            ->where(function ($query) use ($directTableIds, $resolvedRegionId): void {
                $query->whereNull('region_id');

                if ($resolvedRegionId) {
                    $query->orWhere('region_id', $resolvedRegionId);
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

                if ($resolvedRegionId && $priceTable->region_id === $resolvedRegionId) {
                    return 1;
                }

                return 0;
            })
            ->values();
    }

    private function defaultAddressRecord(): ?CustomerAddress
    {
        if ($this->relationLoaded('addresses')) {
            return $this->addresses->firstWhere('default_address', true) ?? $this->addresses->first();
        }

        return $this->addresses()->where('default_address', true)->first() ?? $this->addresses()->first();
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
