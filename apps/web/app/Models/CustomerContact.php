<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerContact extends Model
{
    protected $fillable = ['name', 'position', 'department', 'email', 'phone', 'mobile', 'whatsapp', 'primary_contact', 'active'];

    protected function casts(): array
    {
        return ['primary_contact' => 'boolean', 'active' => 'boolean'];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
