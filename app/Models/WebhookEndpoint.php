<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebhookEndpoint extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'name',
        'url',
        'secret',
        'subscribed_events',
        'is_active',
        'last_dispatched_at',
        'failure_count',
    ];

    protected $casts = [
        'subscribed_events' => 'array',
        'is_active' => 'boolean',
        'last_dispatched_at' => 'datetime',
        'failure_count' => 'integer',
    ];
}
