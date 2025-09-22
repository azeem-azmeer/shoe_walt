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
    Route::get   ('/products',        [ProductController::class, 'indexApi'])
        ->middleware('abilities:products:read');

    Route::post  ('/products',        [ProductController::class, 'store'])
        ->middleware('abilities:products:crud');

    Route::get   ('/products/{id}',   [ProductController::class, 'showApi'])->whereNumber('id')
        ->middleware('abilities:products:read');

    Route::put   ('/products/{id}',   [ProductController::class, 'update'])->whereNumber('id')
        ->middleware('abilities:products:crud');

    Route::delete('/products/{id}',   [ProductController::class, 'destroy'])->whereNumber('id')
        ->middleware('abilities:products:crud');
});


/*=============================
=        Customer APIs        =
=============================*/
Route::middleware('auth:sanctum')->group(function () {

    Route::get   ('/cart/full',    [CartController::class, 'full']);
    Route::get   ('/cart/summary', [CartController::class, 'summary']);
    Route::get   ('/cart/mini',    [CartController::class, 'mini']);
    Route::get('/cart/count', [CartController::class,'count'])->middleware('abilities:cart:read');
    Route::post  ('/cart',         [CartController::class, 'store']);
    Route::delete('/cart/{item}',  [CartController::class, 'destroy'])->whereNumber('item');


    // Wishlist
    Route::post  ('/wishlist',        [WishlistController::class, 'store']);
    Route::delete('/wishlist/{item}', [WishlistController::class, 'destroy'])->whereNumber('item');
   Route::get('/wishlist/count', [WishlistController::class,'count'])->middleware('abilities:wishlist:read');

    // Checkout
    Route::post('/orders', [CheckoutController::class, 'store']);

    // Reviews (CRUD)
     Route::get   ('/reviews',          [ReviewApiController::class, 'index']);   // read
    Route::get   ('/reviews/{id}',     [ReviewApiController::class, 'show']);    // read (if you keep it)
    Route::post  ('/reviews',          [ReviewApiController::class, 'store']);   // create
    Route::put   ('/reviews/{id}',     [ReviewApiController::class, 'update']);  // update
    Route::delete('/reviews/{id}',     [ReviewApiController::class, 'destroy']); // delete
});

// Public (or can be protected if you want)
Route::get('/products/{product}/reviews', [ReviewApiController::class, 'productReviews'])->whereNumber('product');
