<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApiLog extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'license_id',
        'product_id',
        'product_name',
        'script_source',
        'script_folder',
        'endpoint',
        'method',
        'ip',
        'user_agent',
        'license_key_used',
        'hwid_used',
        'roblox_username',
        'roblox_place_id',
        'inject_step',
        'status',
        'http_code',
        'response_message',
        'error_detail',
        'request_meta',
        'response_time_ms',
    ];

    protected function casts(): array
    {
        return [
            'http_code' => 'integer',
            'response_time_ms' => 'integer',
            'request_meta' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function license(): BelongsTo
    {
        return $this->belongsTo(License::class, 'license_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function scopeSuccess($query)
    {
        return $query->whereIn('status', ['success', 'inject_success', 'get_script_success', 'script_served', 'module_served']);
    }

    public function scopeFailed($query)
    {
        return $query->whereNotIn('status', ['success', 'inject_success', 'get_script_success', 'script_served', 'module_served']);
    }

    public function scopeByEndpoint($query, string $endpoint)
    {
        return $query->where('endpoint', $endpoint);
    }

    public function scopeByIp($query, string $ip)
    {
        return $query->where('ip', $ip);
    }

    public function isSuccess(): bool
    {
        return in_array($this->status, ['success', 'inject_success', 'get_script_success', 'script_served', 'module_served'], true);
    }

    public function isSlow(): bool
    {
        return $this->response_time_ms > 200;
    }

    public function isInjectFlow(): bool
    {
        return str_contains($this->endpoint ?? '', 'inject')
            || str_contains($this->endpoint ?? '', 'license/get')
            || str_contains($this->endpoint ?? '', 's/')
            || str_contains($this->endpoint ?? '', 'modules/');
    }
}
