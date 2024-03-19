<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PaymentServiceController;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware([Authenticate::using('sanctum')]);

Route::any('/payment-services/{payment}', [PaymentServiceController::class, 'handle']);

Route::controller(AuthController::class)->prefix('auth')->group(function () {
    Route::middleware([Authenticate::using('sanctum')])->group(function () {
        Route::post('change-password', 'changePassword');
        Route::post('email-verification', 'emailVerification')->middleware(['throttle:1,1']);
        Route::post('verify-email', 'verifyEmail');
    });
    Route::post('register', 'register');
    Route::post('login', 'login');
    Route::post('forget-password', 'forgetPassword');
    Route::post('reset-password', 'resetPassword');
});

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
        Route::post('{order}/update', 'updateOrderItem');
        Route::put('{order}', 'update')->middleware('can:update,order');
    });
    Route::get('', 'index');
    Route::get('{order}', 'find');
});

Route::controller(PaymentController::class)->prefix('payments')->group(function () {
    Route::middleware([Authenticate::using('sanctum')])->group(function () {
        Route::post('', 'store');
    });
});
