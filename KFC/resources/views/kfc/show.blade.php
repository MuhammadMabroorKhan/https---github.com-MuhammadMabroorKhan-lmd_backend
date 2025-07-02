
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
    <p><strong>Status:</strong> {{ ucfirst($order->status) }}</p>
    <p><strong>Payment Status:</strong> {{ ucfirst($order->payment_status) }}</p>
    <!-- <p><strong>Payment Method:</strong> {{ ucfirst($order->payment_method) }}</p> -->

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

@if ($order->status !== 'canceled')
@if ($order->status === 'pending')
<h3>Update Order Status</h3>
    <form method="POST" action="{{ url('/kfc/update-suborder-status') }}">
        @csrf
        <input type="hidden" name="vendor_order_id" value="{{ $order->id }}">
        <input type="hidden" name="status_type" value="order">
        <input type="hidden" name="status" value="processing">
        <button type="submit" class="back-link">Mark as Processing</button>
    </form>
@elseif ($order->status === 'processing')
<h3>Update Order Status</h3>
    <form method="POST" action="{{ url('/kfc/update-suborder-status') }}">
        @csrf
        <input type="hidden" name="vendor_order_id" value="{{ $order->id }}">
        <input type="hidden" name="status_type" value="order">
        <input type="hidden" name="status" value="ready">
        <button type="submit" class="back-link">Mark as Ready</button>
    </form>
@elseif ($order->status === 'picked_up')
<h3>Update Order Status</h3>
    <form method="POST" action="{{ url('/kfc/update-suborder-status') }}">
        @csrf
        <input type="hidden" name="vendor_order_id" value="{{ $order->id }}">
        <input type="hidden" name="status_type" value="order">
        <input type="hidden" name="status" value="handover_confirmed">
        <button type="submit" class="back-link">Mark as Handover Confirmed</button>
    </form>
@endif



@if ($order->payment_status === 'confirmed_by_deliveryboy')
<h3>Update Payment Status</h3>
    <form method="POST" action="{{ url('/kfc/update-suborder-status') }}">
        @csrf
        <input type="hidden" name="vendor_order_id" value="{{ $order->id }}">
        <input type="hidden" name="status_type" value="payment">
        <input type="hidden" name="status" value="confirmed_by_vendor">
        <button type="submit" class="back-link">Confirm Payment (Vendor)</button>
    </form>
@endif

@endif
    <a class="back-link" href="{{ route('kfc.orders') }}">← Back to Orders</a>

</body>
</html>
