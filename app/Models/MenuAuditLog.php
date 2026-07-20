<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuAuditLog extends Model
{
    protected $table = 'menu_audit_logs';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'action',
        'module',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'created_at',
    ];
}
