<?php

namespace App;

use App\Models\Commission;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Transaction;
use App\Models\UserAccess;
use App\Models\UserGroup;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Spatie\MediaLibrary\HasMedia\HasMedia;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;
use Spatie\MediaLibrary\Models\Media;

class User extends Authenticatable implements HasMedia
{
    use Notifiable;
    use HasMediaTrait;
    public $singleFile = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'username', 'password',
        'mobile', 'email', 'status', 'method', 'parent_id',
        'ip_address', 'ip_address2', 'verify_ip',
        'otp', 'otp_hash', 'otp_expires_at', 'otp_attempts',
        'login_attempts', 'enable_ip', 'enable_2fa', 'verify_2fa',
        'secret', 'last_session_id', 'max_active_sessions', 'active_session_ids', 'last_activity',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
        'otp', 'otp_hash', 'secret',
    ];

    protected $casts = [
        'status' => 'integer',
        'method' => 'integer',
        'verify_ip' => 'integer',
        'enable_ip' => 'integer',
        'enable_2fa' => 'integer',
        'verify_2fa' => 'integer',
        'max_active_sessions' => 'integer',
        'login_attempts' => 'integer',
        'otp_attempts' => 'integer',
        'otp_expires_at' => 'datetime',
        'last_activity' => 'datetime',
    ];

    public function setPasswordAttribute($value)
    {
        if (!isset($value) || $value === '') {
            $this->attributes['password'] = $value;
            return;
        }

        if (is_string($value) && preg_match('/^\$2y\$/', $value) && strlen($value) === 60) {
            $this->attributes['password'] = $value;
            return;
        }

        $this->attributes['password'] = Hash::make($value);
    }

    function orders(){
        return $this->hasMany(Order::class);
    }

    function commissions(){
        return $this->hasMany(Commission::class);
    }

    function group(){
        return $this->belongsTo(UserGroup::class);
    }

    function commission(){
        return $this->hasMany(UserAccess::class);
    }

    function payment_history(){
        return $this->hasMany(Payment::class)->orderBy('id', RECORD_ORDER_BY);
    }

    public function children(){
        return $this->hasMany( User::class, 'parent_id', 'id' );
    }

    public function parent(){
        return $this->hasOne( User::class, 'id', 'parent_id' );
    }

    function balance(){
        return $this->hasMany(Transaction::class)->select('balance');
    }

    public function isOnline()
    {
        return Cache::has('user-is-online-'.$this->id);
    }


    public function registerMediaCollections()
    {
        $this
            ->addMediaCollection('avatar')
            ->singleFile()
            ->registerMediaConversions(function (Media $media) {
                $this
                    ->addMediaConversion('thumb')
                    ->width(150)
                    ->height(150)
                    ->sharpen(10)
                    ->nonQueued();
            });
    }
}
