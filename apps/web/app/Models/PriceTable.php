<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PriceTable extends Model
{
    protected $fillable = ['company_id', 'name', 'description', 'active'];

    protected function casts(): array
    {
        return ['active' => 'boolean'];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function regions(): BelongsToMany
    {
        return $this->belongsToMany(Region::class, 'region_price_table')->withTimestamps();
    }

    public function prices(): HasMany
    {
        return $this->hasMany(ProductPrice::class);
    }

    public function customers(): BelongsToMany
    {
        return $this->belongsToMany(Customer::class)->withTimestamps();
    }

    public function salesRepresentatives(): BelongsToMany
    {
        return $this->belongsToMany(SalesRepresentative::class, 'sales_representative_price_table')->withTimestamps();
    }
}
