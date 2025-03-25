<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;

class ViewComposerServiceProvider extends ServiceProvider
{
    public function boot()
    {
        View::composer('biu_layout.admin', function ($view) {
            $view->with('userRole', Auth::user()->role()->first());
        });
    }

    public function register()
    {
        //
    }
}
