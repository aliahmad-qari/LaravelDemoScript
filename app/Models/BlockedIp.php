<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class BlockedIp extends Model
{
    protected $fillable = [
        'ip_address',
        'failed_attempts',
        'reason',
        'blocked_until',
        'is_permanent'
    ];

    protected $casts = [
        'blocked_until' => 'datetime',
        'is_permanent' => 'boolean',
    ];

    public function isBlocked(): bool
    {
        if ($this->is_permanent) {
            return true;
        }

        if ($this->blocked_until && Carbon::now()->lessThan($this->blocked_until)) {
            return true;
        }

        return false;
    }
}
