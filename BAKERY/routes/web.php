<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BAKERYFrontendController;
Route::get('/', function () {
    return view('welcome');
});




Route::get('/', [BAKERYFrontendController::class, 'index'])->name('BAKERY.orders');
Route::get('/orders/{orderId}', [BAKERYFrontendController::class, 'show'])->name('BAKERY.orders.show');
// AJAX route to return HTML partial
// ✅ AJAX route moved here
Route::get('/BAKERY/orders-json', [BAKERYFrontendController::class, 'ordersTable'])->name('BAKERY.orders.json');
Route::post('/BAKERY/update-suborder-status', [BAKERYFrontendController::class, 'updateOrderStatus'])->name('BAKERY.update-status');
