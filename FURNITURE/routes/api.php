<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FURNITUREController;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');



// Route to receive an order
Route::post('order/receive', [FURNITUREController::class, 'receiveOrder']);

Route::get('/order/status', [FURNITUREController::class, 'getOrderStatus']);


Route::get('/rating', [FURNITUREController::class, 'getFURNITURERatingAndReviews']);

// Route to update order status
Route::put('order/{orderId}/status', [FURNITUREController::class, 'updateOrderStatus']);

// Route to mark order as ready
Route::put('order/{orderId}/ready', [FURNITUREController::class, 'markOrderReady']);

// Route to track delivery
Route::get('order/{orderId}/track', [FURNITUREController::class, 'trackDelivery']);



// Route to get all orders for a FURNITURE
Route::get('getAllorders', [FURNITUREController::class, 'getAllOrders']);


Route::post('/orders/{orderId}/delivery/pickup', [FURNITUREController::class, 'updateDeliveryTracking']);


Route::get('/menu/items-with-details', [FURNITUREController::class, 'getItemsWithDetails']);








//New ststuses update
Route::post('/vendors/orders/{id}/cancel', [FURNITUREController::class, 'cancelOrder']);
Route::post('/vendor/orders/{id}/mark-processing', [FURNITUREController::class, 'markAsProcessing']);
Route::post('/vendor/orders/{id}/mark-ready', [FURNITUREController::class, 'markAsReady']);
Route::post('/vendor/orders/{id}/mark-assigned', [FURNITUREController::class, 'markAssigned']);
Route::post('/vendor/orders/{id}/pickup', [FURNITUREController::class, 'pickupOrder']);
Route::post('/vendors/orders/{id}/handover-confirmed', [FURNITUREController::class, 'handoverConfirmed']);
Route::post('/vendors/orders/{id}/in-transit', [FURNITUREController::class, 'inTransit']);
Route::post('/vendors/orders/{id}/delivered', [FURNITUREController::class, 'markDelivered']);
Route::post('/vendors/orders/{id}/confirm-payment/customer', [FURNITUREController::class, 'confirmPaymentByCustomer']);
Route::post('/vendors/orders/{id}/confirm-payment/deliveryboy', [FURNITUREController::class, 'confirmPaymentByDeliveryBoy']);
Route::post('/vendors/orders/{id}/confirm-payment/vendor', [FURNITUREController::class, 'confirmPaymentByVendor']);


Route::get('/order/{orderId}/ratings', [FURNITUREController::class, 'getItemRatingForOrder']);
// Route to add item rating
Route::post('item/rating', [FURNITUREController::class, 'addItemRating']);

//stocks
Route::post('/get-stocks-by-itemdetails', [FURNITUREController::class, 'getStocksByItemDetails']);





Route::post('/itemdetails/update-picture', [FURNITUREController::class, 'updateItemPicture']);


//FOR FURNITURE SIDES (FRONTEND)
Route::get('FURNITUREside/orders', [FURNITUREController::class, 'getAllOrdersWithDetailsForFURNITURE']);
Route::post('/FURNITURE/update-suborder-status', [FURNITUREController::class, 'updateSuborderStatusFromFURNITURE']);
