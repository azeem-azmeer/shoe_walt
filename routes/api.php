<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    ProductController,
    CartController,
    WishlistController
};
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\Api\ReviewApiController;

// quick probe (requires Sanctum token)
Route::get('/user', fn (Request $r) => $r->user())->middleware('auth:sanctum');

/*==========================
=        Admin APIs        =
==========================*/
Route::middleware(['auth:sanctum','admin'])->prefix('admin')->group(function () {
    Route::get   ('/products',        [ProductController::class, 'indexApi']);
    Route::post  ('/products',        [ProductController::class, 'store']);
    Route::get   ('/products/{id}',   [ProductController::class, 'showApi'])->whereNumber('id');
    Route::put   ('/products/{id}',   [ProductController::class, 'update'])->whereNumber('id');
    Route::delete('/products/{id}',   [ProductController::class, 'destroy'])->whereNumber('id');
});

/*=============================
=        Customer APIs        =
=============================*/
Route::middleware('auth:sanctum')->group(function () {
    // Cart
    Route::get   ('/cart/full',      [CartController::class, 'full']);
    Route::get   ('/cart/summary',   [CartController::class, 'summary']);
    Route::get   ('/cart/mini',      [CartController::class, 'mini']);
    Route::get   ('/cart/count',     [CartController::class, 'count']);
    Route::post  ('/cart',           [CartController::class, 'store']);
    Route::patch ('/cart/{item}',    [CartController::class, 'update'])->whereNumber('item');
    Route::delete('/cart/{item}',    [CartController::class, 'destroy'])->whereNumber('item');

    // Wishlist
    Route::post  ('/wishlist',        [WishlistController::class, 'store']);
    Route::delete('/wishlist/{item}', [WishlistController::class, 'destroy'])->whereNumber('item');
    Route::get   ('/wishlist/count',  [WishlistController::class, 'count']);

    // Checkout
    Route::post('/orders', [CheckoutController::class, 'store']);

    // Reviews (CRUD)
    Route::get   ('/reviews',          [ReviewApiController::class, 'index']);
    Route::get   ('/reviews/{id}',     [ReviewApiController::class, 'show']);
    Route::post  ('/reviews',          [ReviewApiController::class, 'store']);
    Route::put   ('/reviews/{id}',     [ReviewApiController::class, 'update']);
    Route::delete('/reviews/{id}',     [ReviewApiController::class, 'destroy']);
});

// Public (or can be protected if you want)
Route::get('/products/{product}/reviews', [ReviewApiController::class, 'productReviews'])->whereNumber('product');
