<?php

namespace App\Http\Controllers;

use App\Models\CustomerCart;
use App\Models\Product;
use App\Models\ProductSize;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    /**
     * POST /api/cart  (AJAX)
     * Body: { product_id, size }
     * Behavior: adds +1 of the chosen size; if the same size is added again,
     * quantity increments. Stock is validated against ProductSize.qty.
     */
   public function index()
{
    if (!\Illuminate\Support\Facades\Auth::check()) {
        return redirect()->route('register');
    }

    $uid  = \Illuminate\Support\Facades\Auth::id();

    $rows = \App\Models\CustomerCart::with('product')
        ->where('user_id', $uid)
        ->latest('added_at')
        ->get();

    $items = $rows->map(function ($row) {
        $p = $row->product;
        return [
            'id'       => $row->id,
            'name'     => $p->product_name ?? 'Product',
            'price'    => (float) ($p->price ?? 0),
            'size'     => $row->size,
            'quantity' => (int) $row->quantity,
            'img'      => $p?->main_image_url,
        ];
    })->values();

    $count    = (int) $rows->sum('quantity');
    $subtotal = (float) $rows->sum(fn($r) => (float)($r->product->price ?? 0) * (int)$r->quantity);
    $subtotal = round($subtotal, 2);
    $tax      = round($subtotal * 0.07, 2); // 7%
    $delivery = 0.00;
    $total    = $subtotal + $tax + $delivery;

    return view('user.cart', compact('items','count','subtotal','tax','delivery','total'));
}

    public function store(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,product_id'],
            'size'       => ['required', 'string', 'max:20'],
        ]);

        $userId    = Auth::id();
        $productId = (int) $data['product_id'];
        $sizeLabel = (string) $data['size'];

        // lookup stock for this size
        $sizeRow = ProductSize::where('product_id', $productId)
            ->where('size', $sizeLabel)
            ->first();

        $available = (int) ($sizeRow->qty ?? 0);
        if ($available <= 0) {
            return response()->json([
                'message'   => "Size {$sizeLabel} is out of stock.",
                'remaining' => 0,
            ], 422);
        }

        // current cart quantity for this product+size
        $item = CustomerCart::where('user_id', $userId)
            ->where('product_id', $productId)
            ->where('size', $sizeLabel)
            ->first();

        $currentQty = (int) ($item->quantity ?? 0);
        $remaining  = $available - $currentQty;

        if ($remaining <= 0) {
            return response()->json([
                'message'   => "Sorry to inform there is no stock left in size {$sizeLabel}.",
                'remaining' => 0,
            ], 422);
        }


        // ok to add one
        if ($item) {
            $item->quantity += 1;
            $item->added_at = now(); // bubble up
            $item->save();
        } else {
            $item = CustomerCart::create([
                'user_id'    => $userId,
                'product_id' => $productId,
                'size'       => $sizeLabel,
                'quantity'   => 1,
                'added_at'   => now(),
            ]);
        }

        $payload = $this->miniPayload($userId);

        return response()->json([
            'message' => 'Added to cart.',
            'count'   => $payload['count'],
            'items'   => $payload['items'],
        ]);
    }

    /**
     * GET /api/cart/mini  (AJAX)
     */
    public function mini()
    {
        if (!Auth::check()) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        return response()->json($this->miniPayload(Auth::id()));
    }

    /**
     * DELETE /api/cart/{item}
     */
    public function destroy(CustomerCart $item)
    {
        if (!Auth::check()) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        abort_if($item->user_id !== Auth::id(), 403);

        $item->delete();

        $payload = $this->miniPayload(Auth::id());

        return response()->json([
            'message' => 'Removed',
            'count'   => $payload['count'],
            'items'   => $payload['items'],
        ]);
    }

    /** Build a compact payload for the mini cart popup */
    private function miniPayload(int $userId): array
    {
        $items = CustomerCart::with('product')
            ->where('user_id', $userId)
            ->latest('added_at')
            ->take(6)
            ->get()
            ->map(function ($row) {
                $p = $row->product;
                return [
                    'id'       => $row->id,
                    'name'     => $p->product_name ?? 'Product',
                    'price'    => (float) ($p->price ?? 0),
                    'size'     => $row->size,
                    'quantity' => (int) $row->quantity,
                    // always send main image url; Product accessor handles fallbacks
                    'img'      => $p?->main_image_url,
                ];
            });

        $count = (int) CustomerCart::where('user_id', $userId)->sum('quantity');

        return ['items' => $items, 'count' => $count];
    }
    /** GET /api/cart/full */
    public function full()
    {
        $this->authorizeAuth();
        [$items, $count, $subtotal] = $this->fullData(Auth::id());

        $tax = round($subtotal * 0.07, 2);
        return response()->json([
            'items'    => $items,
            'count'    => $count,
            'subtotal' => $subtotal,
            'tax'      => $tax,
            'delivery' => 0.00,
            'total'    => $subtotal + $tax,
        ]);
    }

    /** GET /api/cart/summary */
    public function summary()
    {
        $this->authorizeAuth();
        [, $count, $subtotal] = $this->fullData(Auth::id());
        $tax = round($subtotal * 0.07, 2);

        return response()->json([
            'count'    => $count,
            'subtotal' => $subtotal,
            'tax'      => $tax,
            'delivery' => 0.00,
            'total'    => $subtotal + $tax,
        ]);
    }

    // ---------- helpers ----------
    private function authorizeAuth(): void
    {
        if (!Auth::check()) {
            abort(401, 'Unauthenticated');
        }
    }

    /** Return [items, countQty, subtotal] */
    private function fullData(int $userId): array
    {
        $rows = \App\Models\CustomerCart::with('product')
            ->where('user_id', $userId)
            ->latest('added_at')
            ->get();

        $items = $rows->map(function ($row) {
            $p = $row->product;
            return [
                'id'       => $row->id,
                'name'     => $p->product_name ?? 'Product',
                'price'    => (float) ($p->price ?? 0),
                'size'     => $row->size,
                'quantity' => (int) $row->quantity, 
                'img'      => $p?->main_image_url,
            ];
        })->values();

        $countQty = (int) $rows->sum('quantity');
        $subtotal = (float) $rows->sum(fn($r) => (float)($r->product->price ?? 0) * (int)$r->quantity);

        return [$items, $countQty, round($subtotal, 2)];
    }
    public function count()
    {
        if (!\Illuminate\Support\Facades\Auth::check()) {
            return response()->json(['count' => 0]);
        }
        $uid   = \Illuminate\Support\Facades\Auth::id();
        $count = (int) \App\Models\CustomerCart::where('user_id', $uid)->sum('quantity');
        return response()->json(['count' => $count]);
    }

}
