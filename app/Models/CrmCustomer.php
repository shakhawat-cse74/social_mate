<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CrmCustomer extends Model
{
    use HasFactory;

    protected $table = 'crm_customers';

    protected $fillable = [
        'tenant_id',
        'customer_code',
        'type',
        'name',
        'contact_person',
        'email',
        'phone',
        'website',
        'tax_id',
        'billing_address',
        'shipping_address',
        'city',
        'country',
        'payment_terms',
        'credit_limit',
        'total_revenue',
        'status',
        'assigned_user_id',
        'custom_fields',
        'notes',
    ];

    protected $casts = [
        'credit_limit' => 'decimal:2',
        'total_revenue' => 'decimal:2',
        'custom_fields' => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function deals(): HasMany
    {
        return $this->hasMany(CrmDeal::class, 'customer_id');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(CrmActivity::class, 'customer_id');
    }

    public function leads(): HasMany
    {
        return $this->hasMany(CrmLead::class, 'converted_customer_id');
    }
}
