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
        'product_name',
        'description',
        'price',
        'category',
        'status',
        'main_image',
        'view_image1',
        'view_image2',
        'view_image3',
        'view_image4',
        'sold_pieces',
        'stock',
    ];

    protected $casts = [
        'price'       => 'decimal:2',
        'sold_pieces' => 'integer',
        'stock'       => 'integer',
    ];

    // Expose computed URLs automatically when the model is serialized
    protected $appends = [
        'main_image_url',
        'view_images_urls',
    ];

    public function getRouteKeyName()
    {
        return 'product_id';
    }

    /* ===========================
     |  Relationships
     * =========================== */
    public function sizes()
    {
        return $this->hasMany(ProductSize::class, 'product_id', 'product_id')
            ->orderBy('size'); // handy default ordering
    }

    /* ===========================
     |  Accessors (Computed)
     * =========================== */

    /**
     * Main image public URL.
     */
    public function getMainImageUrlAttribute(): string
    {
        return $this->toPublicUrl(
            $this->main_image,
            asset('storage/products/placeholder.png')
        );
    }

    /**
     * Array of public URLs for view images (1–4).
     *
     * @return array<int, string>
     */
    public function getViewImagesUrlsAttribute(): array
    {
        $paths = array_filter([
            $this->view_image1,
            $this->view_image2,
            $this->view_image3,
            $this->view_image4,
        ]);

        return collect($paths)
            ->map(fn ($p) => $this->toPublicUrl($p, null))
            ->filter() // drop nulls if any path missing
            ->values()
            ->all();
    }

    /* ===========================
     |  Helpers
     * =========================== */

    /**
     * Convert a stored path / absolute URL into a public URL.
     * - Accepts:
     *    - "products/foo.avif"
     *    - "public/products/foo.avif"
     *    - "/storage/products/foo.avif"
     *    - "https://cdn.example.com/foo.avif"
     *
     * @param  string|null  $path
     * @param  string|null  $fallback
     * @return string|null
     */
    protected function toPublicUrl(?string $path, ?string $fallback = null): ?string
    {
        if (!$path) {
            return $fallback;
        }

        // Already absolute (http/https) or already a root path (/storage/...)
        if (Str::startsWith($path, ['http://', 'https://', '/'])) {
            return $path;
        }

        // If accidentally stored with "public/" prefix, strip it
        if (Str::startsWith($path, 'public/')) {
            $path = Str::after($path, 'public/');
        }

        // Normal case: stored on "public" disk (storage/app/public/...)
        // Storage::url('products/foo.avif') => "/storage/products/foo.avif"
        if (Storage::disk('public')->exists($path)) {
            return Storage::url($path);
        }

        // Last attempt: assume it's relative to /storage
        return asset('storage/' . ltrim($path, '/'));
    }
}
