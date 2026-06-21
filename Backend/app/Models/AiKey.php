<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiKey extends Model
{
    protected $table = 'ai_keys';

    protected $fillable = [
        'provider',
        'api_key',
        'model',
        'priority',
        'is_active',
        'error_count',
        'usage_count',
        'last_used_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'priority' => 'integer',
        'error_count' => 'integer',
        'usage_count' => 'integer',
        'last_used_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
