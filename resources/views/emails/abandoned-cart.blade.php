<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #4CAF50; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background: #f9f9f9; }
        .item { padding: 10px; border-bottom: 1px solid #ddd; }
        .button { display: inline-block; padding: 10px 20px; background: #4CAF50; color: white; text-decoration: none; border-radius: 5px; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>You Left Items in Your Cart!</h2>
        </div>
        <div class="content">
            <p>Hi {{ $userName }},</p>
            <p>We noticed you left {{ $totalItems }} item(s) in your cart. Don't miss out!</p>
            
            <h3>Your Cart Items:</h3>
            @foreach($cartItems as $item)
            <div class="item">
                <strong>{{ $item->product->name }}</strong><br>
                Quantity: {{ $item->quantity }} | Price: ${{ number_format($item->price, 2) }}
            </div>
            @endforeach
            
            <a href="{{ url('/cart') }}" class="button">Complete Your Purchase</a>
            
            <p style="margin-top: 20px;">If you have any questions, feel free to contact us.</p>
        </div>
    </div>
</body>
</html>
