<?php

namespace App\Models;

use Database\Factories\PaymentTermFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentTerm extends Model
{
    /** @use HasFactory<PaymentTermFactory> */
    use HasFactory;

    protected $fillable = [
        'company_id',
        'code',
        'name',
        'installment_days',
        'minimum_order_amount',
        'description',
        'sort_order',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'installment_days' => 'array',
            'minimum_order_amount' => 'decimal:2',
            'sort_order' => 'integer',
            'active' => 'boolean',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function installmentSummary(): string
    {
        $days = collect($this->installment_days)->map(fn ($day): int => (int) $day)->sort()->values();

        if ($days->isEmpty() || $days->every(fn (int $day): bool => $day === 0)) {
            return 'À vista';
        }

        return $days->implode('/').' dias';
    }
}
