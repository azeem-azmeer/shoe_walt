<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model; 
use Illuminate\Support\Facades\Storage;  
use Illuminate\Support\Str;    

class Product extends Model
{
    protected $table = 'products';
    protected $primaryKey = 'product_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'product_name','description','price','category','status',
        'main_image','view_image1','view_image2','view_image3','view_image4',
        'sold_pieces','stock',
    ];

    protected $casts = [
        'price'       => 'decimal:2',
        'sold_pieces' => 'integer',
        'stock'       => 'integer',
    ];
  public function getRouteKeyName()
    {
        return 'product_id';
    }
    protected $appends = ['main_image_url'];
        public function getMainImageUrlAttribute(): string
    {
        $p = $this->main_image;

        if (!$p) {
            return asset('storage/products/placeholder.png');
        }

        // Already a full URL or /storage path
        if (Str::startsWith($p, ['http://','https://','/'])) {
            return $p;
        }

        // If stored like "public/products/foo.jpg", strip the prefix
        if (Str::startsWith($p, 'public/')) {
            $p = Str::after($p, 'public/');
        }

        // Normal case: stored on "public" disk as "products/foo.jpg"
        if (Storage::disk('public')->exists($p)) {
            return Storage::url($p); // -> /storage/products/foo.jpg
        }

        // Last fallback guess
        return asset('storage/'.$p);
    }
    public function sizes()
    {
        return $this->hasMany(ProductSize::class, 'product_id', 'product_id');
    }

    

}
