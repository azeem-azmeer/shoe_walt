{{-- resources/views/emails/order-receipt.blade.php --}}
@php
  use Carbon\Carbon;

  $tz        = config('app.timezone');
  $created   = $order->created_at ?? now();
  $placedAt  = Carbon::parse($created)->timezone($tz)->format('M j, Y g:ia');
  $eta       = Carbon::parse($created)->timezone($tz)->addDays(15)->format('M j, Y');

  $items     = $order->items ?? collect();
  $currency  = fn($n) => '$'.number_format((float)$n, 2);
@endphp

<x-mail::message>
# Order Confirmation

**Order #{{ $order->id }}**  
Placed on **{{ $placedAt }}**

<x-mail::panel>
**Status:** {{ ucfirst($order->status) }}  
**Estimated Delivery:** **{{ $eta }}**  
We’ll email you tracking details as soon as your package ships.
</x-mail::panel>

## Order Summary
@component('mail::table')
| Item | Size | Qty | Unit Price | Line Total |
|:-----|:----:|:---:|-----------:|-----------:|
@foreach($items as $item)
| {{ $item->product->product_name ?? 'Product' }} | {{ $item->size }} | {{ $item->quantity }} | {{ $currency($item->unit_price) }} | {{ $currency($item->unit_price * $item->quantity) }} |
@endforeach
@endcomponent

@php
  // If you later add tax/shipping, show them here.
@endphp

**Order Total:** {{ $currency($order->total) }}

## Shipping Address
{{ $order->street_address }}

@component('mail::button', ['url' => route('user.orders.show', $order->id)])
View Your Order
@endcomponent

If you have any questions, just reply to this email and our team will be happy to help.

Regards,  
**Shoe Walt**
</x-mail::message>
