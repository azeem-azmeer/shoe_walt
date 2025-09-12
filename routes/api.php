<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    ProductController,
    CartController,
    WishlistController
};
use App\Http\Controllers\CheckoutController;


// quick probe (requires Sanctum token)
Route::get('/user', fn (Request $r) => $r->user())->middleware('auth:sanctum');

/*==========================
=        Admin APIs        =
==========================*/
Route::middleware(['auth:sanctum','admin'])->prefix('admin')->group(function () {
    // Product CRUD
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
    // Cart (CRUD-ish)
    Route::get   ('/cart/full',      [CartController::class, 'full']);        // full list
    Route::get   ('/cart/summary',   [CartController::class, 'summary']);     // totals only
    Route::get   ('/cart/mini',      [CartController::class, 'mini']);        // header mini-cart
    Route::get   ('/cart/count',     [CartController::class, 'count']);       // badge count

    Route::post  ('/cart',           [CartController::class, 'store']);       // add line (create)
    Route::patch ('/cart/{item}',    [CartController::class, 'update'])       // update qty/size
          ->whereNumber('item');
    Route::delete('/cart/{item}',    [CartController::class, 'destroy'])      // remove line
          ->whereNumber('item');

    // Wishlist
    Route::post  ('/wishlist',        [WishlistController::class, 'store']);
    Route::delete('/wishlist/{item}', [WishlistController::class, 'destroy'])->whereNumber('item');
    Route::get   ('/wishlist/count',  [WishlistController::class, 'count']);

    // Orders (place order from current cart via API)
    Route::post('/orders', [CheckoutController::class, 'store']); // returns redirect in web flow; JSON if requested
});
