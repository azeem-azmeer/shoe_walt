<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    /**
     * Show list of logged-in user's orders
     */
   public function index()
{
    $orders = Order::withCount('items')
        ->where('user_id', Auth::id())
        ->latest()
        ->get();

    $orderCount = $orders->count(); // ✅ count of this user's orders

    return view('user.orders', compact('orders', 'orderCount'));
}


    /**
     * Show a single order (only for the logged-in user)
     */
public function show(Order $order)
{
    $user = Auth::user();

    // Prevent viewing others' orders unless admin
    if ($user->role !== 'admin' && $order->user_id !== $user->id) {
        abort(403, 'Unauthorized');
    }

    $order->load(['items.product']);

    // count how many orders this user has in total
    $orderCount = Order::where('user_id', $user->id)->count();

    return view('user.order-show', [
        'order' => $order,
        'items' => $order->items,
        'orderCount' => $orderCount,   // ✅ add this
    ]);
}

}
