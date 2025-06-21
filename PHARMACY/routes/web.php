<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PharmacyFrontendController;
Route::get('/', function () {
    return view('welcome');
});




Route::get('/', [PharmacyFrontendController::class, 'index'])->name('pharmacy.orders');
Route::get('/orders/{orderId}', [PharmacyFrontendController::class, 'show'])->name('pharmacy.orders.show');
// AJAX route to return HTML partial
// ✅ AJAX route moved here
Route::get('/pharmacy/orders-json', [PharmacyFrontendController::class, 'ordersTable'])->name('pharmacy.orders.json');
Route::post('/pharmacy/update-suborder-status', [PharmacyFrontendController::class, 'updateOrderStatus'])->name('pharmacy.update-status');
