<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'company_id',
        'category_id',
        'brand_id',
        'unit_id',
        'external_id',
        'sku',
        'barcode',
        'image_url',
        'name',
        'short_description',
        'description',
        'color',
        'weight_kg',
        'length_cm',
        'width_cm',
        'height_cm',
        'base_price',
        'minimum_quantity',
        'quantity_multiple',
        'allows_fractional_quantity',
        'available_stock',
        'stock_status',
        'published_at',
        'active',
    ];

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
            'minimum_quantity' => 'decimal:3',
            'quantity_multiple' => 'decimal:3',
            'allows_fractional_quantity' => 'boolean',
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
        return (float) ($this->base_price ?? $this->prices->first()?->price ?? 0);
    }

    public function imageSrc(): ?string
    {
        if (! $this->image_url) {
            return null;
        }

        if (str_starts_with($this->image_url, 'http://') || str_starts_with($this->image_url, 'https://')) {
            return $this->image_url;
        }

        return asset($this->image_url);
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
