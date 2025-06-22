<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FURNITUREFrontendController;
Route::get('/', function () {
    return view('welcome');
});




Route::get('/', [FURNITUREFrontendController::class, 'index'])->name('FURNITURE.orders');
Route::get('/orders/{orderId}', [FURNITUREFrontendController::class, 'show'])->name('FURNITURE.orders.show');
// AJAX route to return HTML partial
// ✅ AJAX route moved here
Route::get('/FURNITURE/orders-json', [FURNITUREFrontendController::class, 'ordersTable'])->name('FURNITURE.orders.json');
Route::post('/FURNITURE/update-suborder-status', [FURNITUREFrontendController::class, 'updateOrderStatus'])->name('FURNITURE.update-status');
