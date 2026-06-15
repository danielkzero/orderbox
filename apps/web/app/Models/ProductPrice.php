<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductPrice extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['price' => 'decimal:2', 'minimum_quantity' => 'decimal:3'];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function priceTable(): BelongsTo
    {
        return $this->belongsTo(PriceTable::class);
    }
}
