<?php

namespace App\Providers;

use App\PaymentGateway;
use App\Http\Controllers\SponsorableSponsorshipsController;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(SponsorableSponsorshipsController::class, function () {
            return new SponsorableSponsorshipsController($this->app[PaymentGateway::class]);
        });
    }
}
