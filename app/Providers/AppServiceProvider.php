<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Weiju;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
        $this->app->singleton(Weiju::class, function() {
            return new Weiju();
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
