<!DOCTYPE html>
<html>
<head>
    <title>Shopping Cart</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; }
        .header { background: #2c3e50; color: white; padding: 20px; border-radius: 5px; margin-bottom: 20px; }
        .card { background: white; padding: 20px; border-radius: 5px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .cart-item { padding: 15px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center; }
        .cart-item:last-child { border-bottom: none; }
        .btn { padding: 8px 16px; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-block; }
        .btn-danger { background: #f44336; color: white; }
        .btn-primary { background: #3498db; color: white; }
        .total { font-size: 24px; font-weight: bold; text-align: right; margin-top: 20px; }
        .nav { margin-bottom: 20px; }
        .nav a { display: inline-block; padding: 10px 20px; background: #3498db; color: white; text-decoration: none; border-radius: 5px; margin-right: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Shopping Cart</h1>
        </div>

        <div class="nav">
            <a href="/products">Continue Shopping</a>
            <a href="/dashboard">Dashboard</a>
        </div>

        @if(session('success'))
        <div style="background: #4CAF50; color: white; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
            {{ session('success') }}
        </div>
        @endif

        <div class="card">
            @if($cart->items->count() > 0)
                @foreach($cart->items as $item)
                <div class="cart-item">
                    <div>
                        <h3 style="margin: 0;">{{ $item->product->name }}</h3>
                        <p style="margin: 5px 0;">Quantity: {{ $item->quantity }}</p>
                        <p style="margin: 5px 0; font-weight: bold;">${{ number_format($item->price * $item->quantity, 2) }}</p>
                    </div>
                    <form method="POST" action="/cart/remove/{{ $item->id }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Remove</button>
                    </form>
                </div>
                @endforeach

                <div class="total">
                    Total: ${{ number_format($cart->items->sum(function($item) { return $item->price * $item->quantity; }), 2) }}
                </div>

                <div style="text-align: right; margin-top: 20px;">
                    <button class="btn btn-primary">Proceed to Checkout</button>
                </div>
            @else
                <p>Your cart is empty.</p>
                <a href="/products" class="btn btn-primary">Start Shopping</a>
            @endif
        </div>
    </div>
</body>
</html>
