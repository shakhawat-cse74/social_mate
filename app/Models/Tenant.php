<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'company_email',
        'company_phone',
        'plan_name',
        'status',
        'allowed_channels',
        'ai_enabled',
        'max_agents',
        'subscription_starts_at',
        'subscription_expires_at',
        'settings',
    ];

    protected $casts = [
        'allowed_channels' => 'array',
        'ai_enabled' => 'boolean',
        'max_agents' => 'integer',
        'subscription_starts_at' => 'datetime',
        'subscription_expires_at' => 'datetime',
        'settings' => 'array',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function channels(): HasMany
    {
        return $this->hasMany(Channel::class);
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class);
    }

    public function aiConfigs(): HasMany
    {
        return $this->hasMany(AiConfig::class);
    }

    public function webhookEndpoints(): HasMany
    {
        return $this->hasMany(WebhookEndpoint::class);
    }

    public function quickReplies(): HasMany
    {
        return $this->hasMany(QuickReply::class);
    }

    public function hasChannel(string $platform): bool
    {
        if (empty($this->allowed_channels)) {
            return true;
        }
        return in_array($platform, $this->allowed_channels);
    }

    public function isSubscriptionActive(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        if ($this->subscription_expires_at && $this->subscription_expires_at->isPast()) {
            return false;
        }

        return true;
    }
}
