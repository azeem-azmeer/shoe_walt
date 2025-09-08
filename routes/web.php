<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{FirebaseAuthController, ProductController, WishlistController};

Route::post('/api/auth/firebase', FirebaseAuthController::class)->name('auth.firebase');

Route::view('/index', 'user.index')->name('user.index');
Route::redirect('/', '/index');

Route::get('/products/{product}', [ProductController::class, 'preview'])->name('user.product.preview');
Route::view('/mens',   'user.mens')->name('user.mens');
Route::view('/womans', 'user.womans')->name('user.womans');
Route::view('/kids',   'user.kids')->name('user.kids');

Route::middleware('auth')->group(function () {
    Route::get('/wishlist', [WishlistController::class, 'index'])->name('user.wishlist');
    Route::view('/checkout', 'user.checkout')->name('user.checkout');
});

Route::view('/cart', 'user.cart')->name('user.cart');
Route::get('/bag', fn () => redirect()->route('user.cart'))->name('user.viewbag');

// Dashboard redirect
Route::middleware(['auth','verified'])->get('/dashboard', function () {
    return auth()->user()->role === 'admin'
        ? redirect()->route('admin.dashboard')
        : redirect()->route('user.index');
})->name('dashboard');

// Admin
Route::middleware(['auth','verified','admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::view('/dashboard', 'admin.dashboard')->name('dashboard');
    Route::get('/products',           [ProductController::class, 'index'])->name('products');
    Route::view('/products/create',   'admin.product-create')->name('products.create');
    Route::get('/products/{id}/edit', [ProductController::class, 'edit'])->whereNumber('id')->name('products.edit');
    Route::view('/orders',    'admin.order')->name('orders');
    Route::view('/customers', 'admin.customer')->name('customers');
    Route::view('/reorders',  'admin.reorder')->name('reorders');
    Route::redirect('/', '/admin/dashboard')->name('home');
});
