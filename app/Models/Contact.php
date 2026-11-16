<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Contact extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'name',
        'avatar_url',
        'phone',
        'email',
        'platform',
        'platform_sender_id',
        'lead_status',
        'pipeline_stage',
        'deal_value',
        'tags',
        'custom_attributes',
        'notes',
        'last_active_at',
    ];

    protected $casts = [
        'tags' => 'array',
        'custom_attributes' => 'array',
        'deal_value' => 'decimal:2',
        'last_active_at' => 'datetime',
    ];

    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }
}
