<table>
    <thead>
        <tr>
            <th>Order ID</th>
            <th>Date</th>
            <th>Total</th>
            <th>Status</th>
            <th>From</th>
            <th>Payment</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($orders as $order)
        <tr>
            <td>{{ $order->id }}</td>
            <td>{{ \Carbon\Carbon::parse($order->order_date)->format('d M Y, h:i A') }}</td>
            <td>{{ $order->total_amount }}</td>
            <td>{{ $order->status }}</td>
            <td>{{ $order->order_type }}</td>
            <td>{{ $order->payment_status }}</td>
            <td><a href="{{ route('BAKERY.orders.show', $order->id) }}">View Details</a></td>
        </tr>
        @endforeach
    </tbody>
</table>
