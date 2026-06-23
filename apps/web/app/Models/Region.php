<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Region extends Model
{
    protected $fillable = ['company_id', 'name', 'level', 'state', 'city', 'coverage_type', 'description', 'active'];

    protected function casts(): array
    {
        return ['active' => 'boolean', 'level' => 'integer'];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function representatives(): HasMany
    {
        return $this->hasMany(SalesRepresentative::class);
    }

    public function priceTables(): BelongsToMany
    {
        return $this->belongsToMany(PriceTable::class, 'region_price_table')->withTimestamps();
    }

    public function municipalities(): HasMany
    {
        return $this->hasMany(RegionMunicipality::class);
    }
}
