<?php

use App\Http\Controllers\ItemController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PaymentServiceController;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::any('/payment-services/{payment}', [PaymentServiceController::class, 'handle']);

Route::controller(ItemController::class)->prefix('items')->group(function () {
    Route::middleware([Authenticate::using('sanctum')])->group(function () {
        Route::post('', 'store');
        Route::put('{item}', 'update');
    });
    Route::get('', 'index');
    Route::get('{item}', 'find');
    Route::delete('{item}', 'destroy');
});

Route::controller(OrderController::class)->prefix('orders')->group(function () {
    Route::middleware([Authenticate::using('sanctum')])->group(function () {
        Route::post('', 'store');
        Route::put('{order}', 'update');
    });
    Route::get('', 'index');
    Route::get('{order}', 'find');
});

Route::controller(PaymentController::class)->prefix('payments')->group(function () {
    Route::middleware([Authenticate::using('sanctum')])->group(function () {
        Route::post('', 'store');
    });
});
