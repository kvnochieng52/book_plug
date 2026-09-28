<?php

use App\Http\Controllers\Api\Admin\BookController as AdminBookController;
use App\Http\Controllers\Api\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Api\Admin\DeliveryController as AdminDeliveryController;
use App\Http\Controllers\Api\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Api\Admin\UserController as AdminUserController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\LibraryController;
use App\Http\Controllers\Api\MpesaController;
use App\Http\Controllers\Api\OrderController;
use Illuminate\Support\Facades\Route;

// Public: catalog & auth
Route::get('categories', [CategoryController::class, 'index']);
Route::get('books', [BookController::class, 'index']);
Route::get('books/{book:slug}', [BookController::class, 'show']);

Route::middleware('throttle:10,1')->group(function () {
    Route::post('auth/register', [AuthController::class, 'register']);
    Route::post('auth/login', [AuthController::class, 'login']);
});

// M-Pesa callback is public — Daraja POSTs here from outside
Route::post('mpesa/callback', [MpesaController::class, 'callback'])->name('mpesa.callback');

// PDF download — auth optional (checks entitlement in controller)
Route::get('books/{book:slug}/pdf', [BookController::class, 'downloadPdf'])
    ->name('books.pdf.download')
    ->middleware('auth:sanctum');

// Authenticated user routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('auth/me', [AuthController::class, 'me']);
    Route::post('auth/logout', [AuthController::class, 'logout']);

    Route::apiResource('orders', OrderController::class)->only(['index', 'show', 'store']);
    Route::post('orders/{order:reference}/mpesa/stk', [MpesaController::class, 'initiate'])
        ->middleware('throttle:5,1');
    Route::get('mpesa/transactions/{transaction}', [MpesaController::class, 'status']);

    Route::get('library', [LibraryController::class, 'index']);
    Route::patch('library/{book:slug}/progress', [LibraryController::class, 'updateProgress']);
});

// Admin routes
Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    Route::apiResource('categories', AdminCategoryController::class)->except(['show']);
    Route::apiResource('books', AdminBookController::class);
    Route::get('orders', [AdminOrderController::class, 'index']);
    Route::get('orders/{order:reference}', [AdminOrderController::class, 'show']);
    Route::patch('orders/{order:reference}/status', [AdminOrderController::class, 'updateStatus']);
    Route::get('deliveries', [AdminDeliveryController::class, 'index']);
    Route::put('deliveries/{order:reference}', [AdminDeliveryController::class, 'upsert']);
    Route::get('users', [AdminUserController::class, 'index']);
    Route::patch('users/{user}/role', [AdminUserController::class, 'updateRole']);
});
