<?php

namespace App\Providers;

use App\Services\PushbulletService;
use App\Services\SMSService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * adm_register any application services.
     */
    public function adm_register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }

    public function register(): void
    {
        $this->app->singleton(PushbulletService::class, function ($app) {
            return new PushbulletService();
        });

        $this->app->singleton(SMSService::class, function ($app) {
            return new SMSService();
        });
    }
}
