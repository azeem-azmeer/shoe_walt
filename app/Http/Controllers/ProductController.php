<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductSize;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    /** ----------- WEB pages (server-rendered) ----------- */

    public function index(Request $req)
    {
        $per  = (int) $req->integer('per_page', 12);
        $term = trim((string) $req->input('search', ''));

        $q = \App\Models\Product::query()
            ->select('product_id','product_name','price','category','status','sold_pieces','main_image')
            ->with(['sizes:product_id,size,qty'])
            ->orderByDesc('product_id');

        if ($req->filled('category')) {
            $q->where('category', (string) $req->input('category')); // Men/Women/Kids
        }
        if ($req->filled('status')) {
            $q->where('status', (string) $req->input('status'));     // Active/Inactive
        }
        if ($term !== '') {
            $like = "%{$term}%";
            $q->where(function ($qq) use ($like) {
                $qq->where('product_name', 'like', $like)
                   ->orWhere('category', 'like', $like)
                   ->orWhere('description', 'like', $like);
            });
        }

        $products = $q->paginate($per)->withQueryString();

        $stats = [
            'total'    => \App\Models\Product::count(),
            'active'   => \App\Models\Product::where('status', 'Active')->count(),
            'inactive' => \App\Models\Product::where('status', 'Inactive')->count(),
        ];

        return view('admin.products', compact('products', 'stats'));
    }

    public function edit(int $id)
    {
        $product = Product::with('sizes')->findOrFail($id);

        $viewImages = array_values(array_filter([
            $product->view_image1,
            $product->view_image2,
            $product->view_image3,
            $product->view_image4,
        ]));

        $sizes = $product->sizes
            ->map(fn($s) => ['size' => (string)$s->size, 'qty' => (int)$s->qty])
            ->values()
            ->all();

        return view('admin.product-edit', compact('product','viewImages','sizes'));
    }

    /**
     * Public product preview page
     */
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
            ->map(fn($p) => $p ? Storage::url($p) : null)
            ->filter()
            ->values()
            ->all();

        if (empty($images)) {
            $images = [
                $product->main_image
                    ? Storage::url($product->main_image)
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

        // ---------- Related products (robust, with fallbacks) ----------
        // Base: different product_id
        $base = Product::query()
            ->select('product_id','product_name','price','category','main_image','status')
            ->where('product_id', '<>', $product->product_id);

        // If current has a category, match it loosely (case-insensitive)
        if (!empty($product->category)) {
            $cat = mb_strtolower($product->category);
            $base->whereRaw('LOWER(category) = ?', [$cat]);
        }

        // Try first with status=Active if column exists
        $rows = (clone $base)
            ->when(Schema::hasColumn('products', 'status'), fn($q) => $q->where('status', 'Active'))
            ->latest('product_id')
            ->take(8)
            ->get();

        // Fallback 1: same category (if any), ignore status
        if ($rows->isEmpty()) {
            $rows = (clone $base)->latest('product_id')->take(8)->get();
        }

        // Fallback 2: ignore category and status entirely
        if ($rows->isEmpty()) {
            $rows = Product::query()
                ->select('product_id','product_name','price','category','main_image','status')
                ->where('product_id', '<>', $product->product_id)
                ->latest('product_id')->take(8)->get();
        }

        // Shape for Blade cards
        $related = $rows->map(function ($p) {
            return [
                'id'    => $p->product_id,
                'name'  => $p->product_name,
                'price' => (float) $p->price,
                'cat'   => (string) ($p->category ?? ''),
                'img'   => $p->main_image
                            ? Storage::url($p->main_image)
                            : asset('storage/products/placeholder.png'),
                'href'  => route('user.product.preview', $p->product_id),
            ];
        });
        // ---------------------------------------------------------------

        return view('user.productpreview', [
            'product'  => $product,
            'images'   => $images,
            'sizes'    => $sizes,
            'inStock'  => $inStock,
            'crumbs'   => [
                ['label' => 'Home',  'href' => route('user.index')],
                ['label' => $product->category ?? 'Products', 'href' => '#'],
            ],
            'related'  => $related,           // <<< send normalized cards
            // 'similarProducts' => $rows,     // (no longer needed by your Blade)
        ]);
    }

    /** ----------- API (CRUD) ----------- */

    public function indexApi(Request $req)
    {
        $per  = (int) $req->integer('per_page', 10);
        $page = (int) $req->integer('page', 1);
        $term = trim((string) $req->input('search', ''));

        $q = Product::query()
            ->select('product_id','product_name','price','category','status','sold_pieces','main_image')
            ->with(['sizes:product_id,size,qty'])
            ->orderByDesc('product_id');

        if ($req->filled('status'))   $q->where('status', $req->string('status'));
        if ($req->filled('category')) $q->where('category', $req->string('category'));

        if ($term !== '') {
            $q->where(function ($qq) use ($term) {
                $qq->where('product_name', 'like', "{$term}%")
                   ->orWhere('category', 'like', "{$term}%")
                   ->orWhere('description', 'like', "%{$term}%");
            });
        }

        $p = $q->paginate($per, ['*'], 'page', $page);

        $data = collect($p->items())->map(fn ($item) => [
            'product_id'   => $item->product_id,
            'product_name' => $item->product_name,
            'price'        => (float) $item->price,
            'category'     => $item->category,
            'status'       => $item->status,
            'sold_pieces'  => (int) ($item->sold_pieces ?? 0),
            'main_image'   => $item->main_image,
            'sizes'        => $item->sizes->map(fn ($s) => [
                'size' => $s->size,
                'qty'  => (int) $s->qty
            ])->values(),
        ])->values();

        return response()->json([
            'data' => $data,
            'meta' => [
                'total'        => $p->total(),
                'per_page'     => $p->perPage(),
                'current_page' => $p->currentPage(),
            ],
        ]);
    }

    public function showApi(int $id)
    {
        $p = Product::with('sizes')->find($id);
        if (!$p) return response()->json(['message' => 'Not found'], 404);

        return response()->json([
            'data' => [
                'product_id'   => $p->product_id,
                'product_name' => $p->product_name,
                'description'  => $p->description,
                'price'        => (float) $p->price,
                'category'     => $p->category,
                'status'       => $p->status,
                'main_image'   => $p->main_image,
                'view_images'  => array_values(array_filter([$p->view_image1,$p->view_image2,$p->view_image3,$p->view_image4])),
                'sizes'        => $p->sizes->map(fn ($s) => ['size' => $s->size, 'qty' => (int) $s->qty])->values(),
                'stock'        => (int) $p->sizes->sum('qty'),
                'sold_pieces'  => (int) ($p->sold_pieces ?? 0),
            ]
        ]);
    }

    // ---------- CREATE ----------
    public function store(Request $req)
    {
        try {
            $validated = $req->validate([
                'product_name' => 'required|string|max:255',
                'description'  => 'nullable|string',
                'price'        => 'required|numeric|min:0',
                'category'     => 'required|in:Men,Women,Kids',
                'status'       => 'required|in:Active,Inactive',
                'main_image'   => 'required|file|mimes:jpg,jpeg,png,webp,avif|max:5120',
                'view_images.*'=> 'nullable|file|mimes:jpg,jpeg,png,webp,avif|max:5120',
                'sizes'        => 'required', // array or JSON string
            ]);

            $sizes = $this->parseSizes($validated['sizes']);

            return DB::transaction(function () use ($req, $validated, $sizes) {
                $mainPath = $req->file('main_image')->store('products', 'public');

                $views = [];
                foreach (($req->file('view_images') ?? []) as $file) {
                    $views[] = $file->store('products', 'public');
                }
                $views = array_pad($views, 4, null);

                $p = \App\Models\Product::create([
                    'product_name' => $validated['product_name'],
                    'description'  => $validated['description'] ?? null,
                    'price'        => $validated['price'],
                    'category'     => $validated['category'],
                    'status'       => $validated['status'],
                    'main_image'   => $mainPath,
                    'view_image1'  => $views[0],
                    'view_image2'  => $views[1],
                    'view_image3'  => $views[2],
                    'view_image4'  => $views[3],
                    'sold_pieces'  => 0,
                ]);

                foreach ($sizes as $s) {
                    if (($s['size'] ?? '') !== '' && isset($s['qty'])) {
                        \App\Models\ProductSize::create([
                            'product_id' => $p->product_id,
                            'size'       => (string) $s['size'],
                            'qty'        => (int) $s['qty'],
                        ]);
                    }
                }

                if (Schema::hasColumn('products', 'stock')) {
                    $p->update([
                        'stock' => (int) \App\Models\ProductSize::where('product_id', $p->product_id)->sum('qty')
                    ]);
                }

                return response()->json(['message' => 'Created', 'id' => $p->product_id], 201);
            });
        } catch (\Illuminate\Validation\ValidationException $ve) {
            return response()->json(['message' => 'Validation failed', 'errors' => $ve->errors()], 422);
        } catch (\Throwable $e) {
            Log::error('Product store failed', ['e' => $e]);
            $msg = config('app.debug') ? $e->getMessage() : 'Server error';
            return response()->json(['message' => "Create failed: $msg"], 500);
        }
    }

    // ---------- UPDATE ----------
    public function update(Request $req, int $id)
    {
        try {
            $p = Product::with('sizes')->find($id);
            if (!$p) return response()->json(['message' => 'Not found'], 404);

            $validated = $req->validate([
                'product_name'          => 'required|string|max:255',
                'description'           => 'nullable|string',
                'price'                 => 'required|numeric|min:0',
                'category'              => 'required|in:Men,Women,Kids',
                'status'                => 'required|in:Active,Inactive',
                'main_image'            => 'nullable|file|mimes:jpg,jpeg,png,webp,avif|max:5120',
                'view_images.*'         => 'nullable|file|mimes:jpg,jpeg,png,webp,avif|max:5120',
                'remove_view_images.*'  => 'nullable|boolean',
                'sizes'                 => 'required', // array or JSON string
            ]);

            $sizes = $this->parseSizes($validated['sizes']);

            return DB::transaction(function () use ($req, $validated, $sizes, $p) {
                // main image replace
                if ($req->hasFile('main_image')) {
                    if ($p->main_image) Storage::disk('public')->delete($p->main_image);
                    $p->main_image = $req->file('main_image')->store('products', 'public');
                }

                // view images per-slot update
                $incoming = $req->file('view_images', []);          // e.g. [0 => UploadedFile, 2 => UploadedFile]
                $remove   = $req->input('remove_view_images', []);  // e.g. [1 => "1"]

                $old = [
                    $p->view_image1,
                    $p->view_image2,
                    $p->view_image3,
                    $p->view_image4,
                ];

                foreach ([0,1,2,3] as $i) {
                    if (isset($incoming[$i]) && $incoming[$i]) {
                        if ($old[$i]) Storage::disk('public')->delete($old[$i]);
                        $old[$i] = $incoming[$i]->store('products', 'public');
                    } elseif (isset($remove[$i]) && $remove[$i]) {
                        if ($old[$i]) Storage::disk('public')->delete($old[$i]);
                        $old[$i] = null;
                    }
                }

                [$p->view_image1, $p->view_image2, $p->view_image3, $p->view_image4] = $old;

                // basic fields
                $p->fill([
                    'product_name' => $validated['product_name'],
                    'description'  => $validated['description'] ?? null,
                    'price'        => $validated['price'],
                    'category'     => $validated['category'],
                    'status'       => $validated['status'],
                ])->save();

                // replace sizes
                $p->sizes()->delete();
                foreach ($sizes as $s) {
                    if (($s['size'] ?? '') !== '' && isset($s['qty'])) {
                        ProductSize::create([
                            'product_id' => $p->product_id,
                            'size'       => (string) $s['size'],
                            'qty'        => (int) $s['qty'],
                        ]);
                    }
                }

                // recompute stock if column exists
                if (Schema::hasColumn('products', 'stock')) {
                    $p->update([
                        'stock' => (int) ProductSize::where('product_id', $p->product_id)->sum('qty')
                    ]);
                }

                return response()->json(['message' => 'Updated']);
            });
        } catch (\Illuminate\Validation\ValidationException $ve) {
            return response()->json(['message' => 'Validation failed', 'errors' => $ve->errors()], 422);
        } catch (\Throwable $e) {
            Log::error('Product update failed', ['e' => $e]);
            $msg = config('app.debug') ? $e->getMessage() : 'Server error';
            return response()->json(['message' => "Update failed: $msg"], 500);
        }
    }

    // ---------- DELETE ----------
    public function destroy(int $id)
    {
        try {
            $p = Product::with('sizes')->find($id);
            if (!$p) return response()->json(['message' => 'Not found'], 404);

            // delete files
            $paths = array_filter([
                $p->main_image, $p->view_image1, $p->view_image2, $p->view_image3, $p->view_image4
            ]);
            if (!empty($paths)) {
                Storage::disk('public')->delete($paths);
            }

            $p->sizes()->delete();
            $p->delete();

            return response()->json(['message' => 'Deleted']);
        } catch (\Throwable $e) {
            Log::error('Product destroy failed', ['e' => $e]);
            $msg = config('app.debug') ? $e->getMessage() : 'Server error';
            return response()->json(['message' => "Delete failed: $msg"], 500);
        }
    }

    // ---------- HELPER ----------
    private function parseSizes($raw): array
    {
        if (is_string($raw)) {
            $decoded = json_decode($raw, true);
            return is_array($decoded) ? $decoded : [];
        }
        return is_array($raw) ? $raw : [];
    }

    public function men(Request $req)
    {
        $per   = (int) $req->integer('per_page', 12);
        $size  = trim((string) $req->query('size', ''));
        $sort  = (string) $req->query('sort', '');

        $q = Product::query()
            ->select('product_id','product_name','price','category','status',
                     'main_image','view_image2','view_image3','view_image4')
            ->where('status', 'Active')
            ->where(function ($q) {
                $q->whereRaw('LOWER(category) = ?', ['men'])
                  ->orWhereRaw('LOWER(category) = ?', ['mens'])
                  ->orWhere('category', 'Men');
            });

        if ($size !== '') {
            $q->whereIn('product_id', function ($sub) use ($size) {
                $sub->select('product_id')
                    ->from('product_sizes')
                    ->where('size', $size)
                    ->where('qty', '>', 0);
            });
        }

        match ($sort) {
            'price_asc'  => $q->orderBy('price', 'asc'),
            'price_desc' => $q->orderBy('price', 'desc'),
            default      => $q->orderByDesc('product_id'),
        };

        $products = $q->paginate($per)->withQueryString();

        return view('user.mens', compact('products'));
    }

    public function womans(Request $req)
    {
        $per  = (int) $req->integer('per_page', 12);
        $size = trim((string) $req->query('size', ''));
        $sort = (string) $req->query('sort', '');

        $q = Product::query()
            ->select('product_id','product_name','price','category','status',
                     'main_image','view_image2','view_image3','view_image4')
            ->where('status', 'Active')
            ->where(function ($q) {
                $q->whereRaw('LOWER(category) = ?', ['women'])
                  ->orWhereRaw('LOWER(category) = ?', ['womens'])
                  ->orWhereRaw('LOWER(category) = ?', ['woman'])
                  ->orWhere('category', 'Women');
            });

        if ($size !== '') {
            $q->whereIn('product_id', function ($sub) use ($size) {
                $sub->select('product_id')
                    ->from('product_sizes')
                    ->where('size', $size)
                    ->where('qty', '>', 0);
            });
        }

        match ($sort) {
            'price_asc'  => $q->orderBy('price', 'asc'),
            'price_desc' => $q->orderBy('price', 'desc'),
            default      => $q->orderByDesc('product_id'),
        };

        $products = $q->paginate($per)->withQueryString();

        return view('user.womans', compact('products'));
    }

    public function kids(Request $req)
    {
        $per  = (int) $req->integer('per_page', 12);
        $size = trim((string) $req->query('size', ''));
        $sort = (string) $req->query('sort', '');

        $q = Product::query()
            ->select('product_id','product_name','price','category','status',
                     'main_image','view_image2','view_image3','view_image4')
            ->where('status', 'Active')
            ->where(function ($q) {
                $q->whereRaw('LOWER(category) = ?', ['kids'])
                  ->orWhereRaw('LOWER(category) = ?', ['kid'])
                  ->orWhereRaw('LOWER(category) = ?', ['children'])
                  ->orWhereRaw('LOWER(category) = ?', ['child'])
                  ->orWhereRaw('LOWER(category) = ?', ['boys'])
                  ->orWhereRaw('LOWER(category) = ?', ['girls'])
                  ->orWhere('category', 'Kids');
            });

        if ($size !== '') {
            $q->whereIn('product_id', function ($sub) use ($size) {
                $sub->select('product_id')
                    ->from('product_sizes')
                    ->where('size', $size)
                    ->where('qty', '>', 0);
            });
        }

        match ($sort) {
            'price_asc'  => $q->orderBy('price', 'asc'),
            'price_desc' => $q->orderBy('price', 'desc'),
            default      => $q->orderByDesc('product_id'),
        };

        $products = $q->paginate($per)->withQueryString();

        return view('user.kids', compact('products'));
    }
}
