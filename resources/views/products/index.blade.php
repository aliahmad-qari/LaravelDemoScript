<!DOCTYPE html>
<html>
<head>
    <title>Products</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; }
        .header { background: #2c3e50; color: white; padding: 20px; border-radius: 5px; margin-bottom: 20px; }
        .products { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; }
        .product-card { background: white; padding: 20px; border-radius: 5px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .product-card h3 { margin: 0 0 10px 0; }
        .price { font-size: 24px; font-weight: bold; color: #4CAF50; margin: 10px 0; }
        .btn { padding: 10px 20px; background: #3498db; color: white; text-decoration: none; border-radius: 5px; display: inline-block; border: none; cursor: pointer; }
        .btn:hover { background: #2980b9; }
        .badge { padding: 5px 10px; border-radius: 3px; font-size: 12px; font-weight: bold; background: #9C27B0; color: white; }
        .nav { margin-bottom: 20px; }
        .nav a { display: inline-block; padding: 10px 20px; background: #3498db; color: white; text-decoration: none; border-radius: 5px; margin-right: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Products</h1>
        </div>

        <div class="nav">
            <a href="/dashboard">Dashboard</a>
            <a href="/cart">Cart</a>
        </div>

        @if(session('success'))
        <div style="background: #4CAF50; color: white; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
            {{ session('success') }}
        </div>
        @endif

        <div class="products">
            @forelse($products as $product)
            <div class="product-card">
                @if($product->members_only)
                <span class="badge">MEMBERS ONLY</span>
                @endif
                <h3>{{ $product->name }}</h3>
                <p>{{ Str::limit($product->description, 100) }}</p>
                @if($product->file_size)
                <p><small>File Size: {{ $product->file_size }}</small></p>
                @endif
                <div class="price">${{ number_format($product->price, 2) }}</div>
                <form method="POST" action="/cart/add/{{ $product->id }}">
                    @csrf
                    <button type="submit" class="btn">Add to Cart</button>
                </form>
            </div>
            @empty
            <p>No products available.</p>
            @endforelse
        </div>

        <div style="margin-top: 20px;">
            {{ $products->links() }}
        </div>
    </div>
</body>
</html>
