<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerWishlist extends Model
{
    // If your table is singular (per your screenshot), KEEP this.
    // If your table is plural `customer_wishlists`, remove this line.
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
        // products.product_id is the PK in your app
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }
}
