<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    protected $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    /**
     * Display cart.
     */
    public function index(Request $request)
    {
        $cart = $this->cartService->getCart(
            Auth::id(),
            $request->session()->getId()
        );

        $cart->load('items.product');

        return view('cart.index', compact('cart'));
    }

    /**
     * Add item to cart.
     */
    public function addItem(Request $request, Product $product)
    {
        $request->validate([
            'quantity' => 'nullable|integer|min:1|max:100'
        ]);

        $cart = $this->cartService->getCart(
            Auth::id(),
            $request->session()->getId()
        );

        $this->cartService->addItem($cart, $product, $request->input('quantity', 1));

        return redirect()->back()->with('success', 'Product added to cart successfully.');
    }

    /**
     * Remove item from cart.
     */
    public function removeItem(Request $request, $itemId)
    {
        $cart = $this->cartService->getCart(
            Auth::id(),
            $request->session()->getId()
        );

        $cart->items()->where('id', $itemId)->delete();

        return redirect()->back()->with('success', 'Item removed from cart.');
    }
}
