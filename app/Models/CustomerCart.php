<?php

// app/Models/CustomerCart.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerCart extends Model
{
    public $timestamps = false;              // we only have 'added_at'
    protected $table = 'customer_cart';
    protected $fillable = ['user_id','product_id','size','quantity','added_at'];

    public function user()    { return $this->belongsTo(User::class); }
    public function product() { return $this->belongsTo(Product::class, 'product_id', 'product_id'); } // adjust PK if needed
}
