<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = ['name', 'email', 'password'];

    public function loginIps()
    {
        return $this->hasMany(LoginIp::class);
    }

    public function loginLogs()
    {
        return $this->hasMany(LoginLog::class);
    }
}