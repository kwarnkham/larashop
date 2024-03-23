<?php

use App\Http\Controllers\AddressController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PaymentServiceController;
use App\Http\Controllers\UserController;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware([Authenticate::using('sanctum')]);

Route::any('/payment-services/{payment}', [PaymentServiceController::class, 'handle'])->name('payment_notification_url');

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
        Route::post('{item}/upload-picture', 'uploadPicture');
        Route::put('{item}', 'update');
        Route::delete('{item}', 'destroy');
    });
    Route::get('', 'index');
    Route::get('{item}', 'find');

});

Route::controller(UserController::class)->prefix('users')->group(function () {
    Route::middleware([Authenticate::using('sanctum')])->group(function () {
        Route::post('upload-picture', 'uploadPicture');
        Route::get('', 'index')->middleware(['role:admin']);
        Route::get('{user}', 'find')->middleware(['role:admin']);
        Route::post('{user}/toggle-restriction', 'toggleRestriction')->middleware(['role:admin']);
    });
});

Route::controller(AddressController::class)->prefix('addresses')->group(function () {
    Route::middleware([Authenticate::using('sanctum')])->group(function () {
        Route::post('', 'store');
    });
});

Route::controller(OrderController::class)->prefix('orders')->group(function () {
    Route::middleware([Authenticate::using('sanctum')])->group(function () {
        Route::post('', 'store')->middleware('can:create,App\Models\Order');
        Route::post('{order}/update', 'updateOrderItem')->middleware('can:update,order');
        Route::post('{order}/pay', 'pay')->middleware('can:pay,order');
        Route::post('{order}/receipt', 'downloadReceipt')->middleware('can:view,order');
        Route::put('{order}', 'update')->middleware('can:update,order');
        Route::post('{order}/set-address', 'setAddress')->middleware('can:update,order');
        Route::get('', 'index');
        Route::get('{order}', 'find')->middleware('can:view,order');
    });

});

Route::controller(PaymentController::class)->prefix('payments')->group(function () {
    Route::middleware([Authenticate::using('sanctum')])->group(function () {
        Route::post('', 'store');
    });
});
