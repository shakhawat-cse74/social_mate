<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiConfig extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'name',
        'is_active',
        'provider',
        'model',
        'api_key',
        'system_prompt',
        'knowledge_base',
        'auto_reply_enabled',
        'handover_keywords',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'auto_reply_enabled' => 'boolean',
        'handover_keywords' => 'array',
        'api_key' => 'encrypted',
    ];
}
