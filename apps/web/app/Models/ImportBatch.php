<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImportBatch extends Model
{
    protected $fillable = [
        'company_id',
        'user_id',
        'type',
        'original_filename',
        'status',
        'total_rows',
        'created_rows',
        'updated_rows',
        'failed_rows',
        'errors',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'errors' => 'array',
            'completed_at' => 'datetime',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
