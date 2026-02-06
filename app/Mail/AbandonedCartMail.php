<?php

namespace App\Mail;

use App\Models\Cart;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AbandonedCartMail extends Mailable
{
    use Queueable, SerializesModels;

    public $cart;

    public function __construct(Cart $cart)
    {
        $this->cart = $cart;
    }

    public function build()
    {
        return $this->subject('You left items in your cart!')
                    ->view('emails.abandoned-cart')
                    ->with([
                        'userName' => $this->cart->user->name ?? 'Customer',
                        'cartItems' => $this->cart->items()->with('product')->get(),
                        'totalItems' => $this->cart->items->count()
                    ]);
    }
}
