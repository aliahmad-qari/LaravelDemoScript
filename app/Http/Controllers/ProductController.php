<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    /**
     * Display products list (respecting members-only visibility).
     */
    public function index()
    {
        $query = Product::where('is_active', true);

        // Hide members-only products from guests
        if (!Auth::check()) {
            $query->where('members_only', false);
        }

        $products = $query->orderBy('created_at', 'desc')->paginate(12);

        return view('products.index', compact('products'));
    }

    /**
     * Display single product (with members-only protection).
     */
    public function show(Product $product)
    {
        // Block guests from accessing members-only products
        if ($product->members_only && !Auth::check()) {
            abort(404);
        }

        if (!$product->is_active) {
            abort(404);
        }

        return view('products.show', compact('product'));
    }
}
