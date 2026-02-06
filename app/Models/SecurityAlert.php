<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SecurityAlert extends Model
{
    protected $fillable = [
        'user_id',
        'alert_type',
        'ip_address',
        'country',
        'details',
        'email_sent',
        'email_sent_at'
    ];

    protected $casts = [
        'email_sent' => 'boolean',
        'email_sent_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
