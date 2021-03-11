<?php

namespace App\Providers;

use App\Models\WechatMessage;
use App\Observers\WechatMessageObserver;
use App\Services\Tuling;
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

        $this->app->singleton(Tuling::class, function() {
            return new Tuling();
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        WechatMessage::observe(WechatMessageObserver::class);
    }
}
