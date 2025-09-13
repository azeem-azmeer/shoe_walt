{{-- resources/views/emails/order-receipt.blade.php --}}

<x-mail::message>
{{-- Header with Logo --}}

# 👟 Order Receipt – Shoe Walt

Hi **{{ $order->user->name ?? 'Customer' }}**,  

Thank you for shopping with **Shoe Walt**! 🎉  
Your order **#{{ $order->id }}** has been placed successfully.

---

## 🛍️ Order Summary
@foreach($order->items as $item)
- **{{ $item->product->product_name ?? 'Product' }}**  
  Size: {{ $item->size }} | Qty: {{ $item->quantity }}  
  Price: **${{ number_format($item->unit_price, 2) }}**
@endforeach

---

**Total:** **${{ number_format($order->total, 2) }}**  
**Status:** {{ ucfirst($order->status) }}

---

## 📦 Shipping Address
{{ $order->street_address }}

---

@component('mail::button', ['url' => route('user.orders.show', $order->id)])
🔎 View Your Order
@endcomponent

Thanks for choosing **Shoe Walt** ❤️  
We hope to see you again soon!

<br>
— The Shoe Walt Team

</x-mail::message>
