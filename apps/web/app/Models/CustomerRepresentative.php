<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerRepresentative extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['is_primary' => 'boolean'];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function salesRepresentative(): BelongsTo
    {
        return $this->belongsTo(SalesRepresentative::class);
    }
}
