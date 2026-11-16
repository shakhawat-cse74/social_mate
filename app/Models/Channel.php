<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Channel extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'name',
        'platform',
        'platform_account_id',
        'access_token',
        'verify_token',
        'webhook_secret',
        'is_active',
        'ai_enabled',
        'settings',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'ai_enabled' => 'boolean',
        'settings' => 'array',
        'access_token' => 'encrypted',
    ];

    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }
}
