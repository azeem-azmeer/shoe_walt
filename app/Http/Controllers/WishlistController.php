<?php

namespace App\Http\Controllers;

use App\Models\CustomerWishlist;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{
    // Page: /wishlist
    public function index()
    {
        $items = CustomerWishlist::with('product')
            ->where('user_id', Auth::id())
            ->latest('added_at')
            ->get();

        return view('user.wishlist', [
            'items' => $items,
        ]);
    }

    // POST /api/wishlist  { product_id }
    public function store(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,product_id'],
        ]);

        $userId    = Auth::id();
        $productId = (int) $data['product_id'];

        // If already in wishlist, just report success & current count
        $existing = CustomerWishlist::where('user_id', $userId)
            ->where('product_id', $productId)
            ->first();

        if (!$existing) {
            CustomerWishlist::create([
                'user_id'    => $userId,
                'product_id' => $productId,
                'added_at'   => now(),
            ]);
        }

        $count = (int) CustomerWishlist::where('user_id', $userId)->count();

        return response()->json([
            'message' => $existing ? 'This product is already in your wishlist.' : 'Product has been added to wishlist.',
            'count'   => $count,
        ]);
    }

    // DELETE /api/wishlist/{item}
    public function destroy(CustomerWishlist $item)
    {
        if (!Auth::check()) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        abort_if($item->user_id !== Auth::id(), 403);

        $item->delete();

        $count = (int) CustomerWishlist::where('user_id', Auth::id())->count();

        return response()->json([
            'message' => 'Removed from wishlist.',
            'count'   => $count,
        ]);
    }

    // GET /api/wishlist/count
    public function count()
    {
        if (!Auth::check()) {
            return response()->json(['count' => 0]);
        }

        $count = (int) CustomerWishlist::where('user_id', Auth::id())->count();
        return response()->json(['count' => $count]);
    }
}
