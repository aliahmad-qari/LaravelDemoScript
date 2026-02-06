<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Mail\AbandonedCartMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CartService
{
    /**
     * Get or create cart for user or session.
     */
    public function getCart($userId = null, $sessionId = null): Cart
    {
        $cart = Cart::where(function($query) use ($userId, $sessionId) {
            if ($userId) {
                $query->where('user_id', $userId);
            } else {
                $query->where('session_id', $sessionId);
            }
        })
        ->where('status', 'active')
        ->first();

        if (!$cart) {
            $cart = Cart::create([
                'user_id' => $userId,
                'session_id' => $sessionId,
                'status' => 'active',
                'last_activity' => Carbon::now()
            ]);
        }

        return $cart;
    }

    /**
     * Add item to cart.
     */
    public function addItem(Cart $cart, Product $product, int $quantity = 1): CartItem
    {
        $cartItem = CartItem::updateOrCreate(
            [
                'cart_id' => $cart->id,
                'product_id' => $product->id
            ],
            [
                'quantity' => $quantity,
                'price' => $product->price
            ]
        );

        $cart->update(['last_activity' => Carbon::now()]);

        return $cartItem;
    }

    /**
     * Send abandoned cart reminders.
     */
    public function sendAbandonedCartReminders(): int
    {
        $abandonedThreshold = config('cart.abandoned_threshold', 60); // minutes
        $reminderDelay = config('cart.reminder_delay', 120); // minutes after abandonment

        $carts = Cart::where('status', 'active')
            ->where('reminder_sent', false)
            ->whereNotNull('user_id')
            ->where('last_activity', '<=', Carbon::now()->subMinutes($abandonedThreshold + $reminderDelay))
            ->where('last_activity', '>=', Carbon::now()->subMinutes($abandonedThreshold + $reminderDelay + 60))
            ->with(['user', 'items.product'])
            ->get();

        $sent = 0;

        foreach ($carts as $cart) {
            if ($cart->items->count() > 0 && $cart->user) {
                try {
                    Mail::to($cart->user->email)->queue(new AbandonedCartMail($cart));
                    
                    $cart->update([
                        'reminder_sent' => true,
                        'reminder_sent_at' => Carbon::now()
                    ]);
                    
                    $sent++;
                } catch (\Exception $e) {
                    Log::error("Failed to send abandoned cart email: " . $e->getMessage());
                }
            }
        }

        return $sent;
    }

    /**
     * Mark abandoned carts.
     */
    public function markAbandonedCarts(): int
    {
        $abandonedThreshold = config('cart.abandoned_threshold', 60);

        return Cart::where('status', 'active')
            ->where('last_activity', '<=', Carbon::now()->subMinutes($abandonedThreshold))
            ->update(['status' => 'abandoned']);
    }
}
