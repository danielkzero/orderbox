<?php

namespace App\Models;

use App\Support\BrazilianDocument;
use Database\Factories\CompanyFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    /** @use HasFactory<CompanyFactory> */
    use HasFactory;

    protected $fillable = [
        'corporate_name',
        'trade_name',
        'document',
        'email',
        'phone',
        'active',
    ];

    public function setDocumentAttribute(?string $value): void
    {
        $this->attributes['document'] = BrazilianDocument::normalize($value);
    }

    protected function casts(): array
    {
        return ['active' => 'boolean'];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function paymentMethods(): HasMany
    {
        return $this->hasMany(PaymentMethod::class);
    }

    public function paymentTerms(): HasMany
    {
        return $this->hasMany(PaymentTerm::class);
    }

    public function regions(): HasMany
    {
        return $this->hasMany(Region::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    public function apiClients(): HasMany
    {
        return $this->hasMany(ApiClient::class);
    }
}
