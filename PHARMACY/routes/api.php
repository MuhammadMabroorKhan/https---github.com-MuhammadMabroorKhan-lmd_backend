<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PharmacyController;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');



// Route to receive an order
Route::post('/pharmacy/order/receive', [PharmacyController::class, 'receiveOrder']);

Route::get('/pharmacy/order/status', [PharmacyController::class, 'getOrderStatus']);


Route::get('/pharmacy/rating', [PharmacyController::class, 'getPharmacyRatingAndReviews']);

// Route to update order status
Route::put('order/{orderId}/status', [PharmacyController::class, 'updateOrderStatus']);

// Route to mark order as ready
Route::put('order/{orderId}/ready', [PharmacyController::class, 'markOrderReady']);

// Route to track delivery
Route::get('order/{orderId}/track', [PharmacyController::class, 'trackDelivery']);



// Route to get all orders for a restaurant
Route::get('getAllorders', [PharmacyController::class, 'getAllOrders']);


Route::post('/pharmacy/orders/{orderId}/delivery/pickup', [PharmacyController::class, 'updateDeliveryTracking']);


Route::get('/menu/items-with-details', [PharmacyController::class, 'getItemsWithDetails']);


Route::post('/itemdetails/update-picture', [PharmacyController::class, 'updateItemPicture']);






//New ststuses update
Route::post('/pharmacy/vendors/orders/{id}/cancel', [PharmacyController::class, 'cancelOrder']);
Route::post('/pharmacy/vendor/orders/{id}/mark-processing', [PharmacyController::class, 'markAsProcessing']);
Route::post('/pharmacy/vendor/orders/{id}/mark-ready', [PharmacyController::class, 'markAsReady']);
Route::post('/pharmacy/vendor/orders/{id}/mark-assigned', [PharmacyController::class, 'markAssigned']);
Route::post('/pharmacy/vendor/orders/{id}/pickup', [PharmacyController::class, 'pickupOrder']);
Route::post('/pharmacy/vendors/orders/{id}/handover-confirmed', [PharmacyController::class, 'handoverConfirmed']);
Route::post('/pharmacy/vendors/orders/{id}/in-transit', [PharmacyController::class, 'inTransit']);
Route::post('/pharmacy/vendors/orders/{id}/delivered', [PharmacyController::class, 'markDelivered']);
Route::post('/pharmacy/vendors/orders/{id}/confirm-payment/customer', [PharmacyController::class, 'confirmPaymentByCustomer']);
Route::post('/pharmacy/vendors/orders/{id}/confirm-payment/deliveryboy', [PharmacyController::class, 'confirmPaymentByDeliveryBoy']);
Route::post('/pharmacy/vendors/orders/{id}/confirm-payment/vendor', [PharmacyController::class, 'confirmPaymentByVendor']);


Route::get('/pharmacy/order/{orderId}/ratings', [PharmacyController::class, 'getItemRatingForOrder']);
// Route to add item rating
Route::post('pharmacy/item/rating', [PharmacyController::class, 'addItemRating']);

//stocks
Route::post('/pharmacy/get-stocks-by-itemdetails', [PharmacyController::class, 'getStocksByItemDetails']);








//FOR Pharmacy SIDES (FRONTEND)
Route::get('pharmacyside/orders', [PharmacyController::class, 'getAllOrdersWithDetailsForPharmacy']);
Route::post('/pharmacy/update-suborder-status', [PharmacyController::class, 'updateSuborderStatusFromPharmacy']);
