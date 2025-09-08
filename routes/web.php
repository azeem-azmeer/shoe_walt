<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{FirebaseAuthController, ProductController};
use App\Http\Controllers\WishlistController; 

/*
|--------------------------------------------------------------------------
| Auth endpoint + admin login redirect
|--------------------------------------------------------------------------
*/
Route::post('/api/auth/firebase', FirebaseAuthController::class)->name('auth.firebase');
Route::get('/admin/login', fn () => redirect()->route('login'));


Route::view('/index', 'user.index')->name('user.index');

// Redirect / -> /index
Route::redirect('/', '/index');

Route::get('/products/{product}', [ProductController::class, 'preview'])->name('user.product.preview');
Route::view('/mens',   'user.mens')->name('user.mens');
Route::view('/womans', 'user.womans')->name('user.womans'); 
Route::view('/kids',   'user.kids')->name('user.kids');
Route::middleware('auth')->get('/wishlist', [WishlistController::class, 'index'])->name('user.wishlist');
// View Bag -> cart.blade.php
Route::view('/cart', 'user.cart')->name('user.cart');

// Nice alias for "View Bag" (keeps your existing route names working)
Route::get('/bag', function () {
    return redirect()->route('user.cart');
})->name('user.viewbag');

// Checkout -> checkout.blade.php  (require login)
Route::middleware('auth')->group(function () {
    Route::view('/checkout', 'user.checkout')->name('user.checkout');
});

/*
|--------------------------------------------------------------------------
| DASHBOARD (must be signed in)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth','verified'])->group(function () {
    Route::get('/dashboard', function () {
        return auth()->user()->role === 'admin'
            ? redirect()->route('admin.dashboard')
            : redirect()->route('user.index');
    })->name('dashboard');
});

/*
|--------------------------------------------------------------------------
| ADMIN (signed in + admin only)
|--------------------------------------------------------------------------
| Use your custom 'admin' middleware (or Gate: can:isAdmin).
| Avoid defining /admin/dashboard twice with the same name.
*/
Route::middleware(['auth','verified','admin'])
    ->prefix('admin')->name('admin.')
    ->group(function () {
        Route::view('/dashboard', 'admin.dashboard')->name('dashboard');

        // Products
        Route::get('/products',           [ProductController::class, 'index'])->name('products');
        Route::view('/products/create',   'admin.product-create')->name('products.create');
        Route::get('/products/{id}/edit', [ProductController::class, 'edit'])
            ->whereNumber('id')->name('products.edit');

        // New admin pages
        Route::view('/orders',    'admin.order')->name('orders');
        Route::view('/customers', 'admin.customer')->name('customers');
        Route::view('/reorders',  'admin.reorder')->name('reorders');

        // Optional: /admin → /admin/dashboard
        Route::redirect('/', '/admin/dashboard')->name('home');
    });
