<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;

class NightMaintenanceSetting extends Model
{
    protected $table = 'night_maintenance_settings';
    protected $fillable = ['user_id', 'enabled', 'allowed_start', 'allowed_end', 'timezone'];
    protected $casts = ['enabled' => 'boolean'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
