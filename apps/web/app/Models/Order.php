<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'discounts' => 'array',
            'subtotal' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'order_date' => 'datetime',
            'sent_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function salesRepresentative(): BelongsTo
    {
        return $this->belongsTo(SalesRepresentative::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function priceTable(): BelongsTo
    {
        return $this->belongsTo(PriceTable::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
