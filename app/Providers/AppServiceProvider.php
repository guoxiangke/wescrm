<?php

namespace App\Providers;

use App\Models\WechatMessage;
use App\Observers\WechatMessageObserver;
use App\Services\Tuling;
use Illuminate\Support\ServiceProvider;
use App\Services\Weiju;
use App\Services\Upyun;
use Illuminate\Support\Facades\URL;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if(config("app.env") === 'production'){
            URL::forceScheme('https');
        }

        $this->app->singleton(Weiju::class, function() {
            return new Weiju();
        });

        $this->app->singleton(Tuling::class, function() {
            return new Tuling();
        });

        $this->app->singleton(Upyun::class, function() {
            return new Upyun();
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
