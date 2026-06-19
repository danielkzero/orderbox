<?php

namespace App\Models;

use Database\Factories\OrderDocumentSettingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderDocumentSetting extends Model
{
    /** @use HasFactory<OrderDocumentSettingFactory> */
    use HasFactory;

    public const DEFAULT_COLUMNS = [
        'sequence',
        'image',
        'sku',
        'name',
        'quantity',
        'unit',
        'discounts',
        'unit_price',
        'total',
    ];

    public const AVAILABLE_COLUMNS = [
        'sequence',
        'image',
        'sku',
        'name',
        'quantity',
        'unit',
        'available_stock',
        'table_price',
        'discounts',
        'unit_price',
        'total',
    ];

    protected $fillable = [
        'company_id',
        'columns',
        'image_size',
        'item_order',
        'show_customer_address',
        'show_commercial_terms',
        'show_notes',
        'show_subtotal',
        'show_total_quantity',
        'show_total_weight',
        'show_total',
    ];

    protected function casts(): array
    {
        return [
            'columns' => 'array',
            'show_customer_address' => 'boolean',
            'show_commercial_terms' => 'boolean',
            'show_notes' => 'boolean',
            'show_subtotal' => 'boolean',
            'show_total_quantity' => 'boolean',
            'show_total_weight' => 'boolean',
            'show_total' => 'boolean',
        ];
    }

    public static function defaults(int $companyId): self
    {
        return new self([
            'company_id' => $companyId,
            'columns' => self::DEFAULT_COLUMNS,
            'image_size' => 'medium',
            'item_order' => 'insertion_asc',
            'show_customer_address' => true,
            'show_commercial_terms' => true,
            'show_notes' => true,
            'show_subtotal' => true,
            'show_total_quantity' => false,
            'show_total_weight' => false,
            'show_total' => true,
        ]);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
