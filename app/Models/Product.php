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

    public function preview(Product $product)
    {
        // Load sizes (ordered)
        $product->load(['sizes' => fn($q) => $q->orderBy('size')]);

        // Build gallery from view images (fallback to main image)
        $views = array_filter([
            $product->view_image1,
            $product->view_image2,
            $product->view_image3,
            $product->view_image4,
        ]);

        $images = collect($views)
            ->map(fn($p) => $p ? \Storage::url($p) : null)
            ->filter()
            ->values()
            ->all();

        if (empty($images)) {
            $images = [
                $product->main_image
                    ? \Storage::url($product->main_image)
                    : asset('storage/products/placeholder.png'),
            ];
        }

        // Sizes for Blade
        $sizes = $product->sizes
            ->map(fn($s) => [
                'label'    => (string) $s->size,
                'qty'      => (int) $s->qty,
                'disabled' => (int) $s->qty <= 0,
            ])
            ->values();

        $inStock = (int) $product->sizes->sum('qty') > 0;

        // ---------- Similar products ----------
        $similarProducts = Product::query()
            ->when($product->category, fn($q) => $q->where('category', $product->category))
            ->where('product_id', '!=', $product->product_id)   // your PK
            ->when(
                \Schema::hasColumn('products', 'status'),
                fn($q) => $q->where('status', 'Active')
            )
            ->orderByDesc('product_id')
            ->take(8)
            ->get(['product_id','product_name','price','category','main_image']);

        if ($similarProducts->isEmpty()) {
            $similarProducts = Product::query()
                ->where('product_id', '!=', $product->product_id)
                ->orderByDesc('product_id')
                ->take(8)
                ->get(['product_id','product_name','price','category','main_image']);
        }
        // --------------------------------------

        return view('user.productpreview', [
            'product'          => $product,
            'images'           => $images,
            'sizes'            => $sizes,
            'inStock'          => $inStock,
            'crumbs'           => [
                ['label' => 'Home',  'href' => route('dashboard')],
                ['label' => $product->category ?? 'Products', 'href' => '#'],
            ],
            'similarProducts'  => $similarProducts,
        ]);
    }

}
