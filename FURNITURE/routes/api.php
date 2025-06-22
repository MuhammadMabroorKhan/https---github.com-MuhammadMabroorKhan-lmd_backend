<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FURNITUREController;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');



// Route to receive an order
Route::post('/furniture/order/receive', [FURNITUREController::class, 'receiveOrder']);

Route::get('/furniture/order/status', [FURNITUREController::class, 'getOrderStatus']);


Route::get('/furniture/rating', [FURNITUREController::class, 'getFURNITURERatingAndReviews']);

// Route to update order status
Route::put('order/{orderId}/status', [FURNITUREController::class, 'updateOrderStatus']);

// Route to mark order as ready
Route::put('order/{orderId}/ready', [FURNITUREController::class, 'markOrderReady']);

// Route to track delivery
Route::get('order/{orderId}/track', [FURNITUREController::class, 'trackDelivery']);



// Route to get all orders for a FURNITURE
Route::get('getAllorders', [FURNITUREController::class, 'getAllOrders']);


Route::post('/furniture/orders/{orderId}/delivery/pickup', [FURNITUREController::class, 'updateDeliveryTracking']);


Route::get('/furniture/menu/items-with-details', [FURNITUREController::class, 'getItemsWithDetails']);








//New ststuses update
Route::post('/furniture/vendors/orders/{id}/cancel', [FURNITUREController::class, 'cancelOrder']);
Route::post('/furniture/vendor/orders/{id}/mark-processing', [FURNITUREController::class, 'markAsProcessing']);
Route::post('/furniture/vendor/orders/{id}/mark-ready', [FURNITUREController::class, 'markAsReady']);
Route::post('/furniture/vendor/orders/{id}/mark-assigned', [FURNITUREController::class, 'markAssigned']);
Route::post('/furniture/vendor/orders/{id}/pickup', [FURNITUREController::class, 'pickupOrder']);
Route::post('/furniture/vendors/orders/{id}/handover-confirmed', [FURNITUREController::class, 'handoverConfirmed']);
Route::post('/furniture/vendors/orders/{id}/in-transit', [FURNITUREController::class, 'inTransit']);
Route::post('/furniture/vendors/orders/{id}/delivered', [FURNITUREController::class, 'markDelivered']);
Route::post('/furniture/vendors/orders/{id}/confirm-payment/customer', [FURNITUREController::class, 'confirmPaymentByCustomer']);
Route::post('/furniture/vendors/orders/{id}/confirm-payment/deliveryboy', [FURNITUREController::class, 'confirmPaymentByDeliveryBoy']);
Route::post('/furniture/vendors/orders/{id}/confirm-payment/vendor', [FURNITUREController::class, 'confirmPaymentByVendor']);


Route::get('/furniture/order/{orderId}/ratings', [FURNITUREController::class, 'getItemRatingForOrder']);
// Route to add item rating
Route::post('/furniture/item/rating', [FURNITUREController::class, 'addItemRating']);

//stocks
Route::post('/furniture/get-stocks-by-itemdetails', [FURNITUREController::class, 'getStocksByItemDetails']);





Route::post('/itemdetails/update-picture', [FURNITUREController::class, 'updateItemPicture']);


//FOR FURNITURE SIDES (FRONTEND)
Route::get('FURNITUREside/orders', [FURNITUREController::class, 'getAllOrdersWithDetailsForFURNITURE']);
Route::post('/FURNITURE/update-suborder-status', [FURNITUREController::class, 'updateSuborderStatusFromFURNITURE']);
