<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'failed_login_attempts',
        'locked_until'
    ];

    protected $casts = [
        'locked_until' => 'datetime',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function loginIps()
    {
        return $this->hasMany(LoginIp::class);
    }

    public function loginLogs()
    {
        return $this->hasMany(LoginLog::class);
    }

    public function securityAlerts()
    {
        return $this->hasMany(SecurityAlert::class);
    }

    public function carts()
    {
        return $this->hasMany(Cart::class);
    }
}
