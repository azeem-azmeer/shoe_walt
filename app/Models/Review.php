<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Review extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'reviews';

    protected $fillable = [
        'product_id', 'user_id', 'order_id',
        'rating', 'feedback', 'created_at', 'updated_at',
    ];

    // Cast to Carbon & keep numbers as integers
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'product_id' => 'integer',
        'user_id'    => 'integer',
        'order_id'   => 'integer',
        'rating'     => 'integer',
    ];

    // Let Laravel set timestamps automatically
    public $timestamps = true;
}
