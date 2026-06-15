<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class AuthenticationSession extends Model
{
    use HasUuids;

    protected $fillable = [
        'company_id',
        'user_id',
        'channel',
        'active_slot',
        'session_key_hash',
        'web_session_id',
        'personal_access_token_id',
        'ip_address',
        'user_agent',
        'last_activity_at',
        'revoked_at',
    ];

    protected function casts(): array
    {
        return [
            'active_slot' => 'boolean',
            'last_activity_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }
}
