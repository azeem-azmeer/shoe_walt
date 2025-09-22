<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    FirebaseAuthController,
    ProductController,
    WishlistController,
    CartController,
    CheckoutController,
    OrderController
};
use App\Http\Controllers\Admin\AuthTokenController;

use App\Http\Controllers\UserTokenController;

Route::middleware(['auth'])->post('/user/api-token', [UserTokenController::class, 'mint'])
    ->name('user.api-token');


Route::post('/user/api-token', [UserTokenController::class, 'mint'])
    ->middleware(['auth']); // session-authenticated user mints a Sanctum PAT
/*=========================
=       Auth (web)        =
=========================*/
Route::post('/api/auth/firebase', FirebaseAuthController::class)
    ->name('auth.firebase');

/*=========================
=       Storefront        =
=========================*/
Route::view('/index', 'user.index')->name('user.index');
Route::redirect('/', '/index');

Route::get('/products/{product}', [ProductController::class, 'preview'])
    ->name('user.product.preview');

Route::get('/men',   [ProductController::class, 'men'])->name('user.mens');
Route::get('/women', [ProductController::class, 'womans'])->name('user.womans');
Route::get('/kids',  [ProductController::class, 'kids'])->name('user.kids');

/*========================================
=   Customer area (requires web auth)    =
========================================*/
Route::middleware('auth')->group(function () {
    // Wishlist
    Route::get('/wishlist', [WishlistController::class, 'index'])
        ->name('user.wishlist');

    // Cart
    Route::get('/cart', [CartController::class, 'index'])
        ->name('user.cart');
    Route::get('/bag', fn () => redirect()->route('user.cart'))
        ->name('user.viewbag'); // alias

    // Checkout
    Route::get('/checkout',  [CheckoutController::class, 'index'])
        ->name('user.checkout');
    Route::post('/checkout', [CheckoutController::class, 'store'])
        ->name('user.checkout.store');

    // Orders (list + detail)
    Route::get('/orders', [OrderController::class, 'index'])
        ->name('user.orders');

    // Single order detail (handled by CheckoutController@show)
    Route::get('/orders/{order}', [CheckoutController::class, 'show'])
        ->whereNumber('order')
        ->name('user.orders.show');

    // My reviews page (front-end uses /api for CRUD)
    Route::view('/my-reviews', 'user.my-reviews')
        ->name('reviews.page');
});

/*=========================
=     Dashboard jump      =
=========================*/
Route::middleware(['auth', 'verified'])->get('/dashboard', function () {
    return auth()->user()->role === 'admin'
        ? redirect()->route('admin.dashboard')
        : redirect()->route('user.index');
})->name('dashboard');

/*=========================
=        Admin UI         =
=========================*/
Route::middleware(['auth', 'verified', 'admin'])
    ->prefix('admin')->name('admin.')
    ->group(function () {

        Route::post('/api-token', [AuthTokenController::class, 'mint'])
        ->name('api-token');

        Route::view('/dashboard', 'admin.dashboard')->name('dashboard');

        // Products
        Route::get('/products', [ProductController::class, 'index'])
            ->name('products');
        Route::view('/products/create', 'admin.product-create')
            ->name('products.create');
        Route::get('/products/{id}/edit', [ProductController::class, 'edit'])
            ->whereNumber('id')
            ->name('products.edit');

        // Orders (view; Livewire fills data)
        Route::view('/orders', 'admin.order')->name('orders');

        // Customers / Reorders
        Route::view('/customers', 'admin.customer')->name('customers');
        Route::view('/reorders',  'admin.reorder')->name('reorders');

        // Default admin landing
        Route::redirect('/', '/admin/dashboard')->name('home');

        Route::view('/reviews', 'admin.review')->name('reviews');
    });
