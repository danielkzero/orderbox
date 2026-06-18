<?php

namespace App\Models;

use App\Support\BrazilianDocument;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

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
        $stateRegionId = null;
        $cityRegionId = null;

        if ($defaultAddress && filled($defaultAddress->state)) {
            $state = strtoupper($defaultAddress->state);

            $stateRegionId = Region::query()
                ->where('company_id', $this->company_id)
                ->where('active', true)
                ->where('state', $state)
                ->whereNull('city')
                ->value('id');

            if (filled($defaultAddress->city)) {
                $normalizedCity = Str::lower(Str::ascii($defaultAddress->city));
                $cityRegionId = Region::query()
                    ->where('company_id', $this->company_id)
                    ->where('active', true)
                    ->where('state', $state)
                    ->whereNotNull('city')
                    ->get()
                    ->first(fn (Region $region): bool => Str::lower(Str::ascii($region->city)) === $normalizedCity)
                    ?->id;
            }
        }

        $regionIds = collect([$stateRegionId, $cityRegionId])->filter()->values();

        return PriceTable::query()
            ->where('company_id', $this->company_id)
            ->where('active', true)
            ->where(function ($query) use ($directTableIds, $regionIds): void {
                $query->whereNull('region_id');

                if ($regionIds->isNotEmpty()) {
                    $query->orWhereIn('region_id', $regionIds);
                }

                if ($directTableIds->isNotEmpty()) {
                    $query->orWhereIn('id', $directTableIds);
                }
            })
            ->get()
            ->sortByDesc(function (PriceTable $priceTable) use ($directTableIds, $cityRegionId, $stateRegionId): int {
                if ($directTableIds->contains($priceTable->id)) {
                    return 3;
                }

                if ($cityRegionId && $priceTable->region_id === $cityRegionId) {
                    return 2;
                }

                if ($stateRegionId && $priceTable->region_id === $stateRegionId) {
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
