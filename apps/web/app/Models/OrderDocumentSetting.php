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
        'print_columns',
        'image_size',
        'print_image_size',
        'item_order',
        'print_margin',
        'show_customer_address',
        'print_customer_address',
        'show_commercial_terms',
        'print_commercial_terms',
        'show_notes',
        'print_notes',
        'show_subtotal',
        'print_subtotal',
        'show_total_quantity',
        'print_total_quantity',
        'show_total_weight',
        'print_total_weight',
        'show_total',
        'print_total',
    ];

    protected function casts(): array
    {
        return [
            'columns' => 'array',
            'print_columns' => 'array',
            'show_customer_address' => 'boolean',
            'print_customer_address' => 'boolean',
            'show_commercial_terms' => 'boolean',
            'print_commercial_terms' => 'boolean',
            'show_notes' => 'boolean',
            'print_notes' => 'boolean',
            'show_subtotal' => 'boolean',
            'print_subtotal' => 'boolean',
            'show_total_quantity' => 'boolean',
            'print_total_quantity' => 'boolean',
            'show_total_weight' => 'boolean',
            'print_total_weight' => 'boolean',
            'show_total' => 'boolean',
            'print_total' => 'boolean',
        ];
    }

    public static function defaults(int $companyId): self
    {
        return new self([
            'company_id' => $companyId,
            'columns' => self::DEFAULT_COLUMNS,
            'print_columns' => self::DEFAULT_COLUMNS,
            'image_size' => 'medium',
            'print_image_size' => 'medium',
            'item_order' => 'insertion_asc',
            'print_margin' => 'standard',
            'show_customer_address' => true,
            'print_customer_address' => true,
            'show_commercial_terms' => true,
            'print_commercial_terms' => true,
            'show_notes' => true,
            'print_notes' => true,
            'show_subtotal' => true,
            'print_subtotal' => true,
            'show_total_quantity' => false,
            'print_total_quantity' => false,
            'show_total_weight' => false,
            'print_total_weight' => false,
            'show_total' => true,
            'print_total' => true,
        ]);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
