<?php

use Illuminate\Http\Request;
use App\Http\Controllers\LmdUserController;
use App\Http\Controllers\DeliveryboyController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\SuborderController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\DeliveryboyRatingController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\XYZController;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');






Route::apiResource('xyz', XYZController::class);




// Users Routes
Route::get('/users', [LmdUserController::class, 'index']);
Route::post('/users', [LmdUserController::class, 'store']);
Route::get('/users/{id}', [LmdUserController::class, 'show']);
Route::put('/users/{id}', [LmdUserController::class, 'update']);
Route::delete('/users/{id}', [LmdUserController::class, 'destroy']);

// Deliveryboys Routes
Route::get('/deliveryboys', [DeliveryboyController::class, 'index']);
Route::post('/deliveryboys', [DeliveryboyController::class, 'store']);
Route::get('/deliveryboys/{id}', [DeliveryboyController::class, 'show']);
Route::put('/deliveryboys/{id}', [DeliveryboyController::class, 'update']);
Route::delete('/deliveryboys/{id}', [DeliveryboyController::class, 'destroy']);


// Orders Routes
Route::get('/orders', [OrderController::class, 'index']);
Route::post('/orders', [OrderController::class, 'store']);
Route::get('/orders/{id}', [OrderController::class, 'show']);
Route::put('/orders/{id}', [OrderController::class, 'update']);
Route::delete('/orders/{id}', [OrderController::class, 'destroy']);

// Suborders Routes
Route::get('/suborders', [SuborderController::class, 'index']);
Route::post('/suborders', [SuborderController::class, 'store']);
Route::get('/suborders/{id}', [SuborderController::class, 'show']);
Route::put('/suborders/{id}', [SuborderController::class, 'update']);
Route::delete('/suborders/{id}', [SuborderController::class, 'destroy']);

// Vehicle Routes
Route::get('/vehicles', [VehicleController::class, 'index']);
Route::post('/vehicles', [VehicleController::class, 'store']);
Route::get('/vehicles/{id}', [VehicleController::class, 'show']);
Route::put('/vehicles/{id}', [VehicleController::class, 'update']);
Route::delete('/vehicles/{id}', [VehicleController::class, 'destroy']);

// Deliveryboy Ratings Routes
Route::get('/ratings', [DeliveryboyRatingController::class, 'index']);
Route::post('/ratings', [DeliveryboyRatingController::class, 'store']);
Route::get('/ratings/{id}', [DeliveryboyRatingController::class, 'show']);
Route::put('/ratings/{id}', [DeliveryboyRatingController::class, 'update']);
Route::delete('/ratings/{id}', [DeliveryboyRatingController::class, 'destroy']);

// Customers Routes
Route::get('/customers', [CustomerController::class, 'index']);
Route::post('/customers', [CustomerController::class, 'store']);
Route::get('/customers/{id}', [CustomerController::class, 'show']);
Route::put('/customers/{id}', [CustomerController::class, 'update']);
Route::delete('/customers/{id}', [CustomerController::class, 'destroy']);
