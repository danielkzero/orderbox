<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'available_stock' => 'decimal:3',
            'weight_kg' => 'decimal:3',
            'length_cm' => 'decimal:2',
            'width_cm' => 'decimal:2',
            'height_cm' => 'decimal:2',
            'base_price' => 'decimal:2',
            'published_at' => 'datetime',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function prices(): HasMany
    {
        return $this->hasMany(ProductPrice::class);
    }

    public function displayPrice(): float
    {
        return (float) ($this->base_price ?? $this->prices->sortBy('minimum_quantity')->first()?->price ?? 0);
    }

    public function stockStatusLabel(): string
    {
        return match ($this->stock_status) {
            'OutOfStock' => 'Sem estoque',
            'LowStock' => 'Estoque baixo',
            default => 'Em estoque',
        };
    }

    public function stockStatusIsAvailable(): bool
    {
        return $this->active && $this->stock_status !== 'OutOfStock';
    }
}
