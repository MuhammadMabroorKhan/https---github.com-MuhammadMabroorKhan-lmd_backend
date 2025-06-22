<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\JsonResponse;

class KfcFrontendController extends Controller
{
    public function index()
    {
        $orders = DB::table('orders')
            ->orderBy('order_date', 'desc')
            ->get();

        return view('kfc.index', compact('orders'));
    }

    public function show($orderId)
    {
        $baseUrl = url('/');
        $orderDetails = DB::table('orders')
            ->join('orderdetails', 'orders.id', '=', 'orderdetails.order_id')
            ->join('itemdetails', 'orderdetails.item_detail_id', '=', 'itemdetails.id')
            ->join('items', 'itemdetails.item_ID', '=', 'items.id')
            ->where('orders.id', $orderId)
            ->select(
                'orders.*',
                'orderdetails.qty as item_quantity',
                'orderdetails.unit_price',
                'orderdetails.subtotal',

                'itemdetails.id as item_detail_id',
                'itemdetails.variation_name',
                'itemdetails.cost',
                DB::raw("CASE 
                    WHEN itemdetails.photo IS NULL OR itemdetails.photo = '' 
                    THEN NULL 
                    ELSE CONCAT('$baseUrl/storage/', itemdetails.photo) 
                END as item_photo"),
                'items.name as item_name',
                'items.description'
            )
            ->get();

        if ($orderDetails->isEmpty()) {
            abort(404);
        }

        $order = $orderDetails[0]; // general order info
        return view('kfc.show', compact('order', 'orderDetails'));
    }


    public function ordersTable()
{
    $orders = DB::table('orders')->orderBy('order_date', 'desc')->get();
    return view('kfc.orders-table', compact('orders'));
}


public function updateOrderStatus(Request $request)
{
    $validated = $request->validate([
        'vendor_order_id' => 'required|integer',
        'status_type' => 'required|in:order,payment',
        'status' => 'required|string',
    ]);

    $orderId = $validated['vendor_order_id'];
    $statusType = $validated['status_type'];
    $incomingStatus = strtolower($validated['status']);

    $order = DB::table('orders')->where('id', $orderId)->first();
    if (!$order) {
        return redirect()->back()->with('error', 'Order not found.');
    }

    // Use query builder to update
    if ($statusType === 'payment') {
        if ($incomingStatus === 'confirmed_by_vendor' && $order->payment_status !== 'confirmed_by_deliveryboy') {
            return redirect()->back()->with('error', 'Delivery boy must confirm payment first.');
        }

        $validPaymentStatuses = [
            'pending',
            'confirmed_by_customer',
            'confirmed_by_deliveryboy',
            'confirmed_by_vendor',
        ];

        if (!in_array($incomingStatus, $validPaymentStatuses)) {
            return redirect()->back()->with('error', 'Invalid payment status.');
        }

        DB::table('orders')->where('id', $orderId)->update([
            'payment_status' => $incomingStatus,
        ]);
    }

    if ($statusType === 'order') {
        $validTransitions = [
            'pending' => 'processing',
            'processing' => 'ready',
            'picked_up' => 'handover_confirmed',
            'handover_confirmed' => 'in_transit',
            'in_transit' => 'delivered',
            'delivered' => 'completed',
        ];

        $currentStatus = strtolower($order->status);
        $nextAllowed = $validTransitions[$currentStatus] ?? null;

        if ($nextAllowed !== $incomingStatus) {
            return redirect()->back()->with('error', "Invalid order status transition from '$currentStatus' to '$incomingStatus'.");
        }

        DB::table('orders')->where('id', $orderId)->update([
            'status' => $incomingStatus,
        ]);
    }

    // Map for LMD side
    $statusMap = [
        'processing' => 'in_progress',
        'canceled' => 'cancelled',
    ];
    $mappedStatus = $statusMap[$incomingStatus] ?? $incomingStatus;

    try {
        // $lmdResponse = Http::timeout(60)->post('http://192.168.43.63:8000/api/vendor/update-suborder-status', [
        //     'vendor_order_id' => (string)$orderId,
        //     'status_type' => $statusType,
        //     'status' => $mappedStatus,
        // ]);
 $lmdResponse = Http::withHeaders([
            'Authorization' => 'Bearer electronic-api-key' // ✅ Send API key in header
        ])->timeout(60)->post('http://192.168.43.63:8000/api/vendor/update-suborder-status', [
            'vendor_order_id' => (string)$orderId,
            'status_type' => $statusType,
            'status' => $mappedStatus,
        ]);
        $lmdData = $lmdResponse->json();

        return redirect()->back()->with('success', 'Order status updated successfully.')->with('lmd_response', $lmdData);
    } catch (\Exception $e) {
        return redirect()->back()->with('error', 'LMD update failed: ' . $e->getMessage());
    }
}


}
