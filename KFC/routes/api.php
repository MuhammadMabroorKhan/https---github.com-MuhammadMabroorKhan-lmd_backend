<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ResturantController;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');



// Route to receive an order
Route::post('order/receive', [ResturantController::class, 'receiveOrder']);

Route::get('/order/status', [ResturantController::class, 'getOrderStatus']);


Route::get('/rating', [ResturantController::class, 'getKfcRatingAndReviews']);

// Route to update order status
Route::put('order/{orderId}/status', [ResturantController::class, 'updateOrderStatus']);

// Route to mark order as ready
Route::put('order/{orderId}/ready', [ResturantController::class, 'markOrderReady']);

// Route to track delivery
Route::get('order/{orderId}/track', [ResturantController::class, 'trackDelivery']);

// Route to add item rating
Route::post('item/rating', [ResturantController::class, 'addItemRating']);

// Route to get all orders for a restaurant
Route::get('getAllorders', [ResturantController::class, 'getAllOrders']);


Route::post('/orders/{orderId}/delivery/pickup', [ResturantController::class, 'updateDeliveryTracking']);


Route::get('/menu/items-with-details', [ResturantController::class, 'getItemsWithDetails']);
