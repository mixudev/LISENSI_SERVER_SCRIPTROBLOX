<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $fillable = [
        'discord_id',
        'channel_id',
        'status',
        'processed_by',
        'closed_by',
        'ticket_type',
        'payment_status',
        'payment_amount',
        'payment_qr_url',
        'midtrans_order_id',
        'license_key_generated',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }

    public function isProcessing(): bool
    {
        return $this->status === 'processing';
    }
}
