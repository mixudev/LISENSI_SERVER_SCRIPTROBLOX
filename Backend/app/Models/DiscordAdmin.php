<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiscordAdmin extends Model
{
    protected $table = 'discord_admins';

    protected $fillable = [
        'discord_id',
        'username',
        'avatar_url',
        'note',
        'is_active',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
