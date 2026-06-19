<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalesRepresentative extends Model
{
    protected $fillable = ['company_id', 'region_id', 'user_id', 'code', 'active'];

    protected function casts(): array
    {
        return ['active' => 'boolean'];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function customers(): HasMany
    {
        return $this->hasMany(CustomerRepresentative::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function priceTables(): BelongsToMany
    {
        return $this->belongsToMany(PriceTable::class, 'sales_representative_price_table')->withTimestamps();
    }
}
