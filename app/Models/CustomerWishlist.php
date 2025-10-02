<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerWishlist extends Model
{

    protected $table = 'wishlist';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'product_id',
        'added_at',
    ];

    protected $casts = [
        'user_id'    => 'integer',
        'product_id' => 'integer',
        'added_at'   => 'datetime',
    ];

    public function product()
    {
        
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }
}
