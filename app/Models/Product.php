<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name',
        'description',
        'price',
        'file_size',
        'members_only',
        'is_active'
    ];

    protected $casts = [
        'members_only' => 'boolean',
        'is_active' => 'boolean',
        'price' => 'decimal:2',
    ];

    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }
}
