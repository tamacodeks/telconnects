<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QueuedTopup extends Model
{
    protected $fillable = [
        'request_id', 'provider', 'user_id', 'order_id', 'track_order_id',
        'local_reference', 'provider_reference', 'mobile', 'status', 'payload',
        'provider_response', 'error_message', 'client_ip', 'user_agent',
        'attempts', 'dispatched_at', 'processed_at', 'failed_at', 'refunded_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'provider_response' => 'array',
    ];
}
