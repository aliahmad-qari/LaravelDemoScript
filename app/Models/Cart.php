<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Cart extends Model
{
    protected $fillable = [
        'user_id',
        'session_id',
        'status',
        'last_activity',
        'reminder_sent',
        'reminder_sent_at'
    ];

    protected $casts = [
        'last_activity' => 'datetime',
        'reminder_sent' => 'boolean',
        'reminder_sent_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(CartItem::class);
    }

    public function isAbandoned(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        $abandonedThreshold = config('cart.abandoned_threshold', 60); // minutes
        return $this->last_activity && 
               Carbon::now()->diffInMinutes($this->last_activity) >= $abandonedThreshold;
    }
}
