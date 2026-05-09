<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CheckoutController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\TransactionController;
use Illuminate\Support\Facades\Route;

Route::post('/auth/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me',     [AuthController::class, 'me']);

    Route::get('/settings',   [SettingController::class, 'index']);

    Route::get('/categories', [CategoryController::class, 'index']);

    Route::get('/products',   [ProductController::class, 'index']);

    Route::get('/customers/search', [CustomerController::class, 'search']);
    Route::post('/customers',       [CustomerController::class, 'store']);

    Route::post('/checkout', [CheckoutController::class, 'store']);

    Route::get('/transactions',        [TransactionController::class, 'index']);
    Route::get('/transactions/{id}',   [TransactionController::class, 'show']);
});
