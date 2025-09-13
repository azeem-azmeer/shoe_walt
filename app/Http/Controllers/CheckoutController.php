<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\OrderItem;
use App\Mail\OrderReceiptMail;
use Illuminate\Support\Facades\Mail;
use App\Mail\AdminOrderNotification;


class CheckoutController extends Controller
{
    /** Provide states inline so the view always has $states */
    private function states(): array
    {
        return [
            'CA' => 'California',
            'NY' => 'New York',
            'TX' => 'Texas',
            'FL' => 'Florida',
            'WA' => 'Washington',
            'IL' => 'Illinois',
            'AZ' => 'Arizona',
            'PA' => 'Pennsylvania',
            'OH' => 'Ohio',
            'MI' => 'Michigan',
        ];
    }

    /**
     * GET /checkout
     */
    public function index(Request $request)
    {
        $uid = Auth::id();

        // cart: id, user_id, product_id, size, quantity, added_at
        $rows = DB::table('customer_cart as c')
            ->join('products as p', 'p.product_id', '=', 'c.product_id')
            ->where('c.user_id', $uid)
            ->select([
                'c.id',
                'c.product_id',
                'c.size',
                'c.quantity',
                'p.product_name as name',
                'p.price',
                'p.main_image',
                'p.view_image2',
                'p.view_image3',
                'p.view_image4',
            ])
            ->get();

        // Normalize for the view
        $items = $rows->map(function ($r) {
            $img = collect([$r->main_image, $r->view_image2, $r->view_image3, $r->view_image4])
                ->filter()->first();

            $q         = (int) $r->quantity;
            $lineTotal = (float) $r->price * $q;

            return [
                'id'         => $r->id,
                'product_id' => $r->product_id,
                'name'       => $r->name,
                'price'      => (float) $r->price,
                'size'       => (string) $r->size,
                'quantity'   => $q,
                'qty'        => $q, // alias
                'line_total' => round($lineTotal, 2),
                'img'        => $img
                    ? (preg_match('~^https?://~i', $img)
                        ? $img
                        : (str_starts_with($img, '/storage/') || str_starts_with($img, 'storage/')
                            ? url(ltrim($img, '/'))
                            : \Storage::url($img)))
                    : asset('storage/products/placeholder.png'),
            ];
        });

        $count    = (int) $items->sum(fn ($it) => (int) ($it['quantity'] ?? $it['qty'] ?? 0));
        $subtotal = (float) $items->sum(fn ($it) => (float) ($it['line_total']
                                ?? (($it['price'] ?? 0) * (int) ($it['quantity'] ?? $it['qty'] ?? 0))));
        $taxRate  = 0.10; // demo 10%
        $tax      = round($subtotal * $taxRate, 2);
        $shipping = 0.00; // Free shipping (adjust if needed)
        $total    = round($subtotal + $tax + $shipping, 2);

        $states = $this->states();

        return view('user.checkout', compact(
            'items', 'count', 'subtotal', 'tax', 'shipping', 'total', 'states'
        ));
    }

    /**
     * POST /checkout
     */
    public function store(Request $request)
    {
        $uid  = Auth::id();
        $user = Auth::user();

        $data = $request->validate([
            'street_address' => ['required', 'string', 'max:255'],
            'address2'       => ['nullable', 'string', 'max:255'],
            'city'           => ['required', 'string', 'max:120'],
            'state'          => ['required', 'string', 'max:120'],
            'zip'            => ['required', 'string', 'max:20'],
            'phone'          => ['required', 'string', 'max:30'],
            'email'          => ['required', 'email', 'max:255'],
            'payment_method' => ['required', 'in:card,paypal'],
        ]);

        // Fetch cart
        $cart = DB::table('customer_cart as c')
            ->join('products as p', 'p.product_id', '=', 'c.product_id')
            ->where('c.user_id', $uid)
            ->select(['c.product_id','c.size','c.quantity','p.price'])
            ->get();

        if ($cart->isEmpty()) {
            return back()->withErrors(['cart' => 'Your cart is empty.']);
        }

        // Totals
        $subtotal = (float) $cart->sum(fn ($r) => $r->price * $r->quantity);
        $taxRate  = 0.10;
        $tax      = round($subtotal * $taxRate, 2);
        $shipping = 0.00;
        $total    = round($subtotal + $tax + $shipping, 2);

        // Build address string
        $address2 = $data['address2'] ?? '';
        $fullAddress = trim(implode(', ', array_filter([
            $data['street_address'] . ($address2 !== '' ? ' ' . $address2 : ''),
            "{$data['city']}, {$data['state']} {$data['zip']}",
            "Phone: {$data['phone']}",
            "Email: {$data['email']}",
        ])));

        // Save order + items
        $order = DB::transaction(function () use ($uid, $user, $cart, $total, $fullAddress) {
            $order = Order::create([
                'user_id'        => $user->id,
                'street_address' => $fullAddress,
                'status'         => 'Pending',
                'total'          => $total,
            ]);

            foreach ($cart as $line) {
                // Insert into order_items
                OrderItem::create([
                    'order_id'   => $order->id,
                    'product_id' => (int) $line->product_id,
                    'size'       => (string) $line->size,
                    'quantity'   => (int) $line->quantity,
                    'unit_price' => (float) $line->price,
                ]);

                // Update sold_pieces
                DB::table('products')
                    ->where('product_id', $line->product_id)
                    ->increment('sold_pieces', $line->quantity);

                // Reduce size qty
                DB::table('product_sizes')
                    ->where('product_id', $line->product_id)
                    ->where('size', $line->size)
                    ->decrement('qty', $line->quantity);
            }

            // Clear cart
            DB::table('customer_cart')->where('user_id', $uid)->delete();

              // ✅ Send receipt email
        
                Mail::to($user->email)->send(new OrderReceiptMail($order));

            

            return $order;
        });

        return redirect()
            ->route('user.orders.show', ['order' => $order->id])
            ->with('success', 'Order placed successfully!');
    }

    /**
     * GET /orders/{order}
     */
 public function show(Order $order)
{
    $user = Auth::user();

    if ($user->role !== 'admin') {
        abort_unless($order->user_id === $user->id, 403);
    }

    $order->load(['items.product']);

    // ✅ Count the number of orders placed by this user
    $orderCount = $user->orders()->count();

    return view('user.order-show', [
        'order' => $order,
        'items' => $order->items,
        'orderCount' => $orderCount,
    ]);
}

}
