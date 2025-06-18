<!-- resources/views/kfc/show.blade.php

<!DOCTYPE html>
<html>
<head>
    <title>Order Details</title>
</head>
<body>
    <h2>Order #{{ $order->id }}</h2>
    <p><strong>Date:</strong> {{ $order->order_date }}</p>
    <p><strong>Total Amount:</strong> {{ $order->total_amount }}</p>
    <p><strong>Status:</strong> {{ $order->status }}</p>
    <p><strong>Payment Status:</strong> {{ $order->payment_status }}</p>
    <p><strong>Payment Method:</strong> {{ $order->payment_method }}</p>

    <h3>Items:</h3>
    <ul>
        @foreach ($orderDetails as $item)
            <li>
                <strong>{{ $item->item_name }}</strong> ({{ $item->variation_name }}) - 
                Qty: {{ $item->item_quantity }} | 
                Unit Price: {{ $item->unit_price }} |
                Subtotal: {{ $item->subtotal }} <br>
                <em>{{ $item->description }}</em><br>
                @if ($item->item_photo)
                    <img src="{{ $item->item_photo }}" alt="Photo" width="100">
                @endif
            </li>
        @endforeach
    </ul>

    <a href="{{ route('kfc.orders') }}">Back to Orders</a>
</body>
</html> -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order #{{ $order->id }} Details</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 20px;
        }

        h2 {
            color: #d32f2f;
            margin-bottom: 10px;
        }

        p {
            margin: 6px 0;
            font-size: 1rem;
        }

        h3 {
            margin-top: 30px;
            color: #333;
            border-bottom: 2px solid #d32f2f;
            padding-bottom: 5px;
        }

        .items-container {
            margin-top: 15px;
        }

        .item-card {
            background-color: white;
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            display: flex;
            flex-direction: column;
        }

        .item-card img {
            max-width: 120px;
            border-radius: 6px;
            margin-top: 10px;
        }

        .item-card strong {
            font-size: 1.1rem;
            color: #d32f2f;
        }

        .item-card em {
            color: #666;
        }

        .back-link {
            display: inline-block;
            margin-top: 25px;
            text-decoration: none;
            color: white;
            background-color: #d32f2f;
            padding: 10px 20px;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .back-link:hover {
            background-color: #a52828;
        }

        @media (max-width: 600px) {
            .item-card {
                font-size: 0.95rem;
            }

            .item-card img {
                max-width: 100px;
            }

            p {
                font-size: 0.95rem;
            }
        }
    </style>
</head>
<body>

    <h2>Order #{{ $order->id }}</h2>
    <p><strong>Date:</strong> {{ \Carbon\Carbon::parse($order->order_date)->format('d M Y, h:i A') }}</p>
    <p><strong>Total Amount:</strong> {{ $order->total_amount }}</p>
    <!-- <p><strong>Status:</strong> {{ ucfirst($order->status) }}</p>
    <p><strong>Payment Status:</strong> {{ ucfirst($order->payment_status) }}</p>
    <p><strong>Payment Method:</strong> {{ ucfirst($order->payment_method) }}</p> -->

    <h3>Ordered Items</h3>
    <div class="items-container">
        @foreach ($orderDetails as $item)
            <div class="item-card">
                <strong>{{ $item->item_name }}</strong> ({{ $item->variation_name }})<br>
                Qty: {{ $item->item_quantity }} |
                Unit Price: {{ $item->unit_price }} |
                Subtotal: {{ $item->subtotal }}<br>
                <em>{{ $item->description }}</em><br>
                @if ($item->item_photo)
                    <img src="{{ $item->item_photo }}" alt="Item Photo">
                @endif
            </div>
        @endforeach
    </div>

    <a class="back-link" href="{{ route('kfc.orders') }}">← Back to Orders</a>

</body>
</html>
