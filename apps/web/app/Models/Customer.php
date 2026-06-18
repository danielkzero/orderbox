<?php

namespace App\Models;

use App\Services\ApplicablePriceTableService;
use App\Support\BrazilianDocument;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class Customer extends Model
{
    protected $fillable = [
        'company_id',
        'region_id',
        'client_reference',
        'corporate_name',
        'trade_name',
        'document',
        'state_registration',
        'email',
        'phone',
        'credit_limit',
        'active',
        'version',
    ];

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
        return app(ApplicablePriceTableService::class)->forCustomer($this);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
