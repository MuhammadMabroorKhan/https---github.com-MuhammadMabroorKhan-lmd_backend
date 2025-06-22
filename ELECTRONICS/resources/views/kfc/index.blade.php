
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Electronics Orders</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            background-color: #f4f4f4;
        }

        h1 {
            color: #d32f2f;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        th, td {
            padding: 12px 15px;
            border: 1px solid #ddd;
            text-align: left;
        }

        th {
            background-color: #d32f2f;
            color: white;
        }

        a {
            color: #007bff;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            table, thead, tbody, th, td, tr {
                display: block;
            }

            th {
                display: none;
            }

            td {
                border: none;
                position: relative;
                padding-left: 50%;
                margin-bottom: 10px;
            }

            td::before {
                position: absolute;
                top: 12px;
                left: 15px;
                width: 45%;
                font-weight: bold;
                white-space: nowrap;
            }

            td:nth-of-type(1)::before { content: "Order ID"; }
            td:nth-of-type(2)::before { content: "Date"; }
            td:nth-of-type(3)::before { content: "Total"; }
            td:nth-of-type(4)::before { content: "Status"; }
            td:nth-of-type(5)::before { content: "Payment"; }
            td:nth-of-type(6)::before { content: "Action"; }
        }
    </style>
</head>
<body>

    <h1>All Electronics Orders</h1>

    <div id="ordersTable">
        @include('kfc.orders-table', ['orders' => $orders])
    </div>

    <script>
        function fetchOrders() {
            fetch("{{ route('kfc.orders.json') }}")
                .then(response => response.text())
                .then(html => {
                    document.getElementById("ordersTable").innerHTML = html;
                })
                .catch(error => console.error('Error fetching orders:', error));
        }

        setInterval(fetchOrders, 10000); // Every 10 seconds
    </script>

</body>
</html>
