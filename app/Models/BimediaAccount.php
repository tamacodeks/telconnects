<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BimediaAccount extends Model
{
    protected $table='bimedia_accounts';
    
    protected $fillable = [
        'name',
        'comcod',
        'tpvcod',
        'authorization',
        'ip_address',
        'web_service_url',
        'web_service_location',
        'active'
    ];
}
