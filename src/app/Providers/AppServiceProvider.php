<?php

namespace App\Providers;

use App\Http\Requests\LoginRequest;
use App\Http\Responses\LogoutResponse;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Http\Requests\LoginRequest as FortifyLoginRequest;
use Laravel\Fortify\Contracts\LogoutResponse as LogoutResponseContract;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(FortifyLoginRequest::class, LoginRequest::class);
        $this->app->singleton(LogoutResponseContract::class, LogoutResponse::class);
        $this->app->singleton(LogoutResponseContract::class, LogoutResponse::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}