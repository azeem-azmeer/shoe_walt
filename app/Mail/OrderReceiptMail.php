<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderReceiptMail extends Mailable
{
    use Queueable, SerializesModels;

    public $order;

    /**
     * Create a new message instance.
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Build the message.
     */
 // OrderReceiptMail.php
public function build()
{
    return $this->subject('Your Shoe Walt Order Receipt - #' . $this->order->id)
                ->markdown('emails.orders.receipt')   
                ->with(['order' => $this->order]);
}


}
