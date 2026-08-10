<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TopupConfirmAttempt extends Model
{
    protected $fillable = [
        'request_id',
        'layer',
        'provider',
        'user_id',
        'username',
        'mobile',
        'amount',
        'status',
        'lock_key',
        'internal_reference',
        'provider_reference',
        'order_id',
        'transaction_id',
        'ip',
        'user_agent',
        'referer',
        'origin',
        'error_message',
        'metadata',
        'locked_at',
        'completed_at',
    ];
}
