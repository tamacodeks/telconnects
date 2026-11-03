<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TopupProcessingSetting extends Model
{
    protected $fillable = ['user_id', 'mode'];
}
