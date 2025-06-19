<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\KfcFrontendController;
Route::get('/', function () {
    return view('welcome');
});




Route::get('/', [KfcFrontendController::class, 'index'])->name('kfc.orders');
Route::get('/orders/{orderId}', [KfcFrontendController::class, 'show'])->name('kfc.orders.show');
// AJAX route to return HTML partial
// ✅ AJAX route moved here
Route::get('/kfc/orders-json', [KfcFrontendController::class, 'ordersTable'])->name('kfc.orders.json');
Route::post('/kfc/update-suborder-status', [KfcFrontendController::class, 'updateOrderStatus'])->name('kfc.update-status');
