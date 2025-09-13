<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'street_address',
        'status',
        'total',
    ];
    protected $casts = [
    'total'      => 'decimal:2',
    'order_date' => 'datetime',
];


    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
    public function user()  { return $this->belongsTo(\App\Models\User::class); }

    public function customer()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
