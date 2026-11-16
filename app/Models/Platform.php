<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Platform extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'name',
        'slug',
        'short_code',
        'color',
        'description',
        'outbound_api_url',
        'is_builtin',
        'is_active',
        'config',
    ];

    protected $casts = [
        'is_builtin' => 'boolean',
        'is_active' => 'boolean',
        'config' => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function channels(): HasMany
    {
        return $this->hasMany(Channel::class, 'platform', 'slug');
    }
}
