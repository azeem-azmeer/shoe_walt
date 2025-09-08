<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use App\Http\Responses\LoginResponse;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use App\Models\CustomerCart;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
          $this->app->singleton(LoginResponseContract::class, LoginResponse::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('components.nav', function ($view) {
            static $memo = null; // avoid duplicate queries if nav renders twice
            if ($memo === null) {
                $memo = Auth::check()
                    ? (int) CustomerCart::where('user_id', Auth::id())->sum('quantity')
                    : 0;
            }
            $view->with('cartCount', $memo);
        });
    }
}
