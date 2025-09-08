<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{ProductController, CartController};
use App\Http\Controllers\WishlistController; 

// sanity probe
Route::get('/user', fn (Request $r) => $r->user())->middleware('auth:sanctum');

// ===== Admin product CRUD (already in your file) =====
Route::middleware(['auth:sanctum','admin'])
    ->prefix('admin')->group(function () {
        Route::get   ('/products',        [ProductController::class, 'indexApi']);
        Route::post  ('/products',        [ProductController::class, 'store']);
        Route::get   ('/products/{id}',   [ProductController::class, 'showApi'])->whereNumber('id');
        Route::put   ('/products/{id}',   [ProductController::class, 'update'])->whereNumber('id');
        Route::delete('/products/{id}',   [ProductController::class, 'destroy'])->whereNumber('id');
    });

// ===== Customer cart CRUD (auth user) =====
Route::middleware('auth:sanctum')->group(function () {
    Route::post  ('/cart',        [CartController::class, 'store']);         // add / increment
    Route::get   ('/cart/mini',   [CartController::class, 'mini']);          // small list for popup
    Route::delete('/cart/{item}', [CartController::class, 'destroy']);       // remove a row
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/wishlist', [WishlistController::class, 'store']);      // add
    Route::delete('/wishlist/{item}', [WishlistController::class, 'destroy']); // remove
    Route::get('/wishlist/count', [WishlistController::class, 'count']); // badge
});