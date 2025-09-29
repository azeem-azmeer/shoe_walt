<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\Api\ReviewApiController;

/*
|--------------------------------------------------------------------------
| API Routes
| Prefix: /api (added automatically by RouteServiceProvider)
|--------------------------------------------------------------------------
*/

Route::get('/ping', fn () => response()->json(['ok' => true]));

// Probe current user (requires a valid Sanctum token)
Route::get('/user', fn (Request $r) => $r->user())
    ->middleware('auth:sanctum')
    ->name('api.user');

/*==========================
=        Admin APIs        =
==========================*/
Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {

    // Products (Admin)
    Route::controller(ProductController::class)->group(function () {
        Route::get   ('/products',            'indexApi')
            ->middleware('abilities:products:read')
            ->name('api.admin.products.index');

        Route::post  ('/products',            'store')
            ->middleware('abilities:products:crud')
            ->name('api.admin.products.store');

        Route::get   ('/products/{id}',       'showApi')
            ->whereNumber('id')
            ->middleware('abilities:products:read')
            ->name('api.admin.products.show');

        Route::put   ('/products/{id}',       'update')
            ->whereNumber('id')
            ->middleware('abilities:products:crud')
            ->name('api.admin.products.update');

        Route::delete('/products/{id}',       'destroy')
            ->whereNumber('id')
            ->middleware('abilities:products:crud')
            ->name('api.admin.products.destroy');
    });
});

/*=============================
=        Customer APIs        =
=============================*/
Route::middleware('auth:sanctum')->group(function () {

    // Cart
    Route::controller(CartController::class)->group(function () {
        Route::get   ('/cart/full',    'full'   )->name('api.cart.full');
        Route::get   ('/cart/summary', 'summary')->name('api.cart.summary');
        Route::get   ('/cart/mini',    'mini'   )->name('api.cart.mini');

        // If you want ability checks on reads, keep these:
        Route::get   ('/cart/count',   'count'  )
            ->middleware('abilities:cart:read')
            ->name('api.cart.count');

        Route::post  ('/cart',         'store'  )->name('api.cart.store');
        Route::delete('/cart/{item}',  'destroy')->whereNumber('item')->name('api.cart.destroy');
    });

    // Wishlist
    Route::controller(WishlistController::class)->group(function () {
        Route::post  ('/wishlist',         'store'  )->name('api.wishlist.store');
        Route::delete('/wishlist/{item}',  'destroy')->whereNumber('item')->name('api.wishlist.destroy');

        Route::get   ('/wishlist/count',   'count')
            ->middleware('abilities:wishlist:read')
            ->name('api.wishlist.count');
    });

    // Checkout
    Route::post('/orders', [CheckoutController::class, 'store'])->name('api.orders.store');

    // Reviews (authenticated CRUD)
    Route::controller(ReviewApiController::class)->group(function () {
        Route::get   ('/reviews',        'index'  )->name('api.reviews.index');   // list
        Route::get   ('/reviews/{id}',   'show'   )->whereNumber('id')->name('api.reviews.show');
        Route::post  ('/reviews',        'store'  )->name('api.reviews.store');   // create
        Route::put   ('/reviews/{id}',   'update' )->whereNumber('id')->name('api.reviews.update');
        Route::delete('/reviews/{id}',   'destroy')->whereNumber('id')->name('api.reviews.destroy');
    });
});

// Public product reviews (per product)
Route::get('/products/{product}/reviews', [ReviewApiController::class, 'productReviews'])
    ->whereNumber('product')
    ->name('api.products.reviews');
