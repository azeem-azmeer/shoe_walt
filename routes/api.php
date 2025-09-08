<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{ProductController, CartController};
use App\Http\Controllers\WishlistController;

// sanity probe (authenticated user)
Route::get('/user', fn (Request $r) => $r->user())->middleware('auth:sanctum');

// Admin APIs
Route::middleware(['auth:sanctum','admin'])->prefix('admin')->group(function () {
    Route::get   ('/products',        [ProductController::class, 'indexApi']);
    Route::post  ('/products',        [ProductController::class, 'store']);
    Route::get   ('/products/{id}',   [ProductController::class, 'showApi'])->whereNumber('id');
    Route::put   ('/products/{id}',   [ProductController::class, 'update'])->whereNumber('id');
    Route::delete('/products/{id}',   [ProductController::class, 'destroy'])->whereNumber('id');
});

// Customer APIs
Route::middleware('auth:sanctum')->group(function () {
    Route::post  ('/cart',        [CartController::class, 'store']);
    Route::get   ('/cart/mini',   [CartController::class, 'mini']);
    Route::delete('/cart/{item}', [CartController::class, 'destroy']);
    // Add these for the full page (used by JS after delete)
    Route::get('/cart/full',    [CartController::class, 'full']);      // all items
    Route::get('/cart/summary', [CartController::class, 'summary']);   // totals only
    Route::middleware('auth:sanctum')->get('/cart/count', [CartController::class, 'count']);

    Route::post  ('/wishlist',        [WishlistController::class, 'store']);
    Route::delete('/wishlist/{item}', [WishlistController::class, 'destroy']);
    Route::get   ('/wishlist/count',  [WishlistController::class, 'count']);
});
