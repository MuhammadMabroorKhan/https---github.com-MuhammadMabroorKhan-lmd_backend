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



// Route to get all orders for a restaurant
Route::get('getAllorders', [ResturantController::class, 'getAllOrders']);


Route::post('/orders/{orderId}/delivery/pickup', [ResturantController::class, 'updateDeliveryTracking']);


Route::get('/menu/items-with-details', [ResturantController::class, 'getItemsWithDetails']);








//New ststuses update
Route::post('/vendors/orders/{id}/cancel', [ResturantController::class, 'cancelOrder']);
Route::post('/vendor/orders/{id}/mark-processing', [ResturantController::class, 'markAsProcessing']);
Route::post('/vendor/orders/{id}/mark-ready', [ResturantController::class, 'markAsReady']);
Route::post('/vendor/orders/{id}/mark-assigned', [ResturantController::class, 'markAssigned']);
Route::post('/vendor/orders/{id}/pickup', [ResturantController::class, 'pickupOrder']);
Route::post('/vendors/orders/{id}/handover-confirmed', [ResturantController::class, 'handoverConfirmed']);
Route::post('/vendors/orders/{id}/in-transit', [ResturantController::class, 'inTransit']);
Route::post('/vendors/orders/{id}/delivered', [ResturantController::class, 'markDelivered']);
Route::post('/vendors/orders/{id}/confirm-payment/customer', [ResturantController::class, 'confirmPaymentByCustomer']);
Route::post('/vendors/orders/{id}/confirm-payment/deliveryboy', [ResturantController::class, 'confirmPaymentByDeliveryBoy']);
Route::post('/vendors/orders/{id}/confirm-payment/vendor', [ResturantController::class, 'confirmPaymentByVendor']);


Route::get('/order/{orderId}/ratings', [ResturantController::class, 'getItemRatingForOrder']);
// Route to add item rating
Route::post('item/rating', [ResturantController::class, 'addItemRating']);

//stocks
Route::post('/get-stocks-by-itemdetails', [ResturantController::class, 'getStocksByItemDetails']);
