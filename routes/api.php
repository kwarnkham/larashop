<?php

use App\Http\Controllers\ItemController;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::controller(ItemController::class)->prefix('items')->group(function () {
    Route::middleware([Authenticate::using('sanctum')])->group(function () {
        Route::post('', 'store');
        Route::put('{item}', 'update');
    });
    Route::get('', 'index');
    Route::get('{item}', 'find');
});
