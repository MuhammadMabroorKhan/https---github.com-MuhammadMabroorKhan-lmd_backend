<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BAKERYController;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');



// Route to receive an order
Route::post('/BAKERY/order/receive', [BAKERYController::class, 'receiveOrder']);

Route::get('/BAKERY/order/status', [BAKERYController::class, 'getOrderStatus']);


Route::get('/BAKERY/rating', [BAKERYController::class, 'getBAKERYRatingAndReviews']);

// Route to update order status
Route::put('order/{orderId}/status', [BAKERYController::class, 'updateOrderStatus']);

// Route to mark order as ready
Route::put('order/{orderId}/ready', [BAKERYController::class, 'markOrderReady']);

// Route to track delivery
Route::get('order/{orderId}/track', [BAKERYController::class, 'trackDelivery']);



// Route to get all orders for a BAKERY
Route::get('getAllorders', [BAKERYController::class, 'getAllOrders']);


Route::post('/BAKERY/orders/{orderId}/delivery/pickup', [BAKERYController::class, 'updateDeliveryTracking']);


Route::get('/BAKERY/menu/items-with-details', [BAKERYController::class, 'getItemsWithDetails']);








//New ststuses update
Route::post('/BAKERY/vendors/orders/{id}/cancel', [BAKERYController::class, 'cancelOrder']);
Route::post('/BAKERY/vendor/orders/{id}/mark-processing', [BAKERYController::class, 'markAsProcessing']);
Route::post('/BAKERY/vendor/orders/{id}/mark-ready', [BAKERYController::class, 'markAsReady']);
Route::post('/BAKERY/vendor/orders/{id}/mark-assigned', [BAKERYController::class, 'markAssigned']);
Route::post('/BAKERY/vendor/orders/{id}/pickup', [BAKERYController::class, 'pickupOrder']);
Route::post('/BAKERY/vendors/orders/{id}/handover-confirmed', [BAKERYController::class, 'handoverConfirmed']);
Route::post('/BAKERY/vendors/orders/{id}/in-transit', [BAKERYController::class, 'inTransit']);
Route::post('/BAKERY/vendors/orders/{id}/delivered', [BAKERYController::class, 'markDelivered']);
Route::post('/BAKERY/vendors/orders/{id}/confirm-payment/customer', [BAKERYController::class, 'confirmPaymentByCustomer']);
Route::post('/BAKERY/vendors/orders/{id}/confirm-payment/deliveryboy', [BAKERYController::class, 'confirmPaymentByDeliveryBoy']);
Route::post('/BAKERY/vendors/orders/{id}/confirm-payment/vendor', [BAKERYController::class, 'confirmPaymentByVendor']);


Route::get('/BAKERY/order/{orderId}/ratings', [BAKERYController::class, 'getItemRatingForOrder']);
// Route to add item rating
Route::post('/BAKERY/item/rating', [BAKERYController::class, 'addItemRating']);

//stocks
Route::post('/BAKERY/get-stocks-by-itemdetails', [BAKERYController::class, 'getStocksByItemDetails']);





Route::post('/itemdetails/update-picture', [BAKERYController::class, 'updateItemPicture']);


//FOR BAKERY SIDES (FRONTEND)
Route::get('BAKERYside/orders', [BAKERYController::class, 'getAllOrdersWithDetailsForBAKERY']);
Route::post('/BAKERY/update-suborder-status', [BAKERYController::class, 'updateSuborderStatusFromBAKERY']);
