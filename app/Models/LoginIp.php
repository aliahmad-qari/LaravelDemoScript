<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoginIp extends Model
{
    protected $fillable = ['user_id', 'ip_address', 'country', 'user_agent'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}