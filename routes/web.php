<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    FirebaseAuthController,
    ProductController,
    WishlistController,
    CartController
};
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\OrderController;

/*====================
=   Auth (web)       =
====================*/
Route::post('/api/auth/firebase', FirebaseAuthController::class)->name('auth.firebase');

/*====================
=   Storefront       =
====================*/
Route::view('/index', 'user.index')->name('user.index');
Route::redirect('/', '/index');

Route::get('/products/{product}', [ProductController::class, 'preview'])->name('user.product.preview');
Route::get('/men',   [ProductController::class, 'men'])->name('user.mens');
Route::get('/women', [ProductController::class, 'womans'])->name('user.womans');
Route::get('/kids',  [ProductController::class, 'kids'])->name('user.kids');

/*========================================
=   Customer area (requires web auth)    =
========================================*/
Route::middleware(['auth'])->group(function () {
    // Wishlist page
    Route::get('/wishlist', [WishlistController::class, 'index'])->name('user.wishlist');

    // Cart page
    Route::get('/cart', [CartController::class, 'index'])->name('user.cart');
    Route::get('/bag', fn () => redirect()->route('user.cart'))->name('user.viewbag'); // alias

    // Checkout (page + submit)
    Route::get ('/checkout',       [CheckoutController::class, 'index'])->name('user.checkout');
    Route::post('/checkout',       [CheckoutController::class, 'store'])->name('user.checkout.store');

    // Order details page (after placing order)
    Route::get('/orders/{order}',  [CheckoutController::class, 'show'])->name('user.orders.show')
         ->whereNumber('order');
  
});


Route::middleware(['auth'])->group(function () {
    Route::get('/orders', [OrderController::class, 'index'])->name('user.orders');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('user.orders.show');
});


/*=========================
=  Dashboard redirect     =
=========================*/
Route::middleware(['auth','verified'])->get('/dashboard', function () {
    return auth()->user()->role === 'admin'
        ? redirect()->route('admin.dashboard')
        : redirect()->route('user.index');
})->name('dashboard');

/*=====================
=      Admin UI       =
=====================*/
Route::middleware(['auth','verified','admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::view('/dashboard', 'admin.dashboard')->name('dashboard');

        // Products CRUD pages
        Route::get('/products',           [ProductController::class, 'index'])->name('products');
        Route::view('/products/create',   'admin.product-create')->name('products.create');
        Route::get('/products/{id}/edit', [ProductController::class, 'edit'])
             ->whereNumber('id')->name('products.edit');

        // Other admin pages
        Route::view('/orders',    'admin.order')->name('orders');
        Route::view('/customers', 'admin.customer')->name('customers');
        Route::view('/reorders',  'admin.reorder')->name('reorders');

        Route::redirect('/', '/admin/dashboard')->name('home');
    });
