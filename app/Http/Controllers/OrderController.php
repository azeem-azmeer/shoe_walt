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

        $orderCount = $orders->count(); 

        return view('user.orders', compact('orders', 'orderCount'));
    }
}
