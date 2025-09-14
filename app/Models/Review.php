<?php
namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Review extends Model
{
   protected $connection = 'mongodb';
    protected $collection = 'reviews';

    protected $fillable = ['user_id','order_id','product_id','rating','feedback'];
    protected $casts = ['rating' => 'integer'];
}
