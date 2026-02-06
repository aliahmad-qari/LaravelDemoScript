<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CountryRestriction extends Model
{
    protected $fillable = [
        'country_code',
        'country_name',
        'action',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
