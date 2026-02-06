<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DisposableEmailDomain extends Model
{
    protected $fillable = [
        'domain',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
