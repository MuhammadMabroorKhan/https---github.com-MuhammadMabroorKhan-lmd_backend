<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

}
