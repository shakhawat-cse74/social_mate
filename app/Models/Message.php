<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'conversation_id',
        'contact_id',
        'direction',
        'sender_type',
        'platform_message_id',
        'message_type',
        'content',
        'attachments',
        'status',
        'ai_generated',
        'metadata',
        'reactions',
    ];

    protected $casts = [
        'attachments' => 'array',
        'ai_generated' => 'boolean',
        'metadata' => 'array',
        'reactions' => 'array',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }
}
