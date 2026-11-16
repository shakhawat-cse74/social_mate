<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class CrmLead extends Model
{
    use HasFactory;

    protected $table = 'crm_leads';

    protected $fillable = [
        'tenant_id',
        'lead_code',
        'title',
        'first_name',
        'last_name',
        'company_name',
        'email',
        'phone',
        'source',
        'channel_id',
        'contact_id',
        'status',
        'priority',
        'estimated_budget',
        'assigned_user_id',
        'converted_customer_id',
        'converted_at',
        'lost_reason',
        'notes',
    ];

    protected $casts = [
        'estimated_budget' => 'decimal:2',
        'converted_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function channel(): BelongsTo
    {
        return $this->belongsTo(Channel::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function convertedCustomer(): BelongsTo
    {
        return $this->belongsTo(CrmCustomer::class, 'converted_customer_id');
    }

    public function deals(): HasMany
    {
        return $this->hasMany(CrmDeal::class, 'lead_id');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(CrmActivity::class, 'lead_id');
    }

    /**
     * Convert this qualified lead into an ERP Customer and Deal.
     */
    public function convertToCustomerAndDeal(?string $dealTitle = null, ?float $amount = null): array
    {
        // 1. Create or Find Customer
        $customer = CrmCustomer::create([
            'tenant_id' => $this->tenant_id,
            'customer_code' => 'CUST-' . strtoupper(Str::random(5)),
            'type' => $this->company_name ? 'company' : 'individual',
            'name' => $this->company_name ?: trim(($this->first_name ?? '') . ' ' . ($this->last_name ?? '')) ?: 'Client #' . $this->id,
            'contact_person' => trim(($this->first_name ?? '') . ' ' . ($this->last_name ?? '')),
            'email' => $this->email,
            'phone' => $this->phone,
            'assigned_user_id' => $this->assigned_user_id,
            'notes' => "Converted from Lead {$this->lead_code}: {$this->title}",
        ]);

        // 2. Create Deal if title or estimated budget given
        $deal = null;
        $finalAmount = $amount ?? $this->estimated_budget;
        if ($dealTitle || $finalAmount > 0) {
            $deal = CrmDeal::create([
                'tenant_id' => $this->tenant_id,
                'deal_code' => 'DEAL-' . rand(1000, 9999),
                'title' => $dealTitle ?: "Opportunity for {$customer->name}",
                'customer_id' => $customer->id,
                'lead_id' => $this->id,
                'stage' => 'proposal',
                'amount' => $finalAmount,
                'probability' => 40,
                'expected_close_date' => now()->addDays(30),
                'assigned_user_id' => $this->assigned_user_id,
            ]);
        }

        // 3. Mark Lead as Converted
        $this->update([
            'status' => 'converted',
            'converted_customer_id' => $customer->id,
            'converted_at' => now(),
        ]);

        return ['customer' => $customer, 'deal' => $deal];
    }
}
