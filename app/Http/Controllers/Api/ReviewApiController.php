<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewApiController extends Controller
{
    // GET /api/reviews?order_id=36
    public function index(Request $r)
    {
        $r->validate(['order_id' => 'required|integer']);
        $rev = Review::where('user_id', Auth::id())
            ->where('order_id', (int)$r->order_id)
            ->orderBy('_id', 'desc')
            ->first();

        // keep response shape stable for the JS
        return response()->json(['data' => $rev ? [$rev] : []]);
    }

    // POST /api/reviews
    public function store(Request $r)
    {
        $data = $r->validate([
            'order_id'   => 'required|integer',
            'product_id' => 'nullable|integer',
            'rating'     => 'required|integer|min:1|max:5',
            'feedback'   => 'required|string|max:1000',
        ]);

        // ensure the order belongs to the current user
        $ok = Order::where('id', $data['order_id'])
                   ->where('user_id', Auth::id())
                   ->exists();
        abort_unless($ok, 404);

        // allow re-creating after delete
        $rev = Review::updateOrCreate(
            ['user_id' => Auth::id(), 'order_id' => $data['order_id']],
            [
                'product_id' => $data['product_id'] ?? null,
                'rating'     => $data['rating'],
                'feedback'   => $data['feedback'],
            ]
        );

        return response()->json($rev, 201);
    }

    public function update(Request $r, string $id)
    {
        // If not found, return 404 JSON (don’t crash pages)
        $rev = Review::where('_id', $id)->first();
        if (!$rev) {
            return response()->json(['message' => 'Review not found'], 404);
        }
        abort_unless($rev->user_id === Auth::id(), 403);

        $data = $r->validate([
            'rating'   => 'sometimes|integer|min:1|max:5',
            'feedback' => 'sometimes|string|max:1000',
        ]);

        $rev->fill($data)->save();
        return response()->json($rev);
    }

    public function destroy(string $id)
    {
        // Be idempotent: deleting a non-existent review should still succeed
        $rev = Review::where('_id', $id)->first();
        if ($rev) {
            abort_unless($rev->user_id === Auth::id(), 403);
            $rev->delete();
        }
        // 204 No Content is perfect here
        return response()->noContent();
    }
}
