<?php

namespace LaravelStream\Redis;

use Illuminate\Support\ServiceProvider;
use LaravelStream\Redis\Commands\MakeStreamChannelCommand;
use LaravelStream\Redis\Commands\StreamingCommand;
use LaravelStream\Redis\Facades\Stream;
use LaravelStream\Redis\Helpers\StreamHelper;

class StreamServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind('stream', function () {
            return new StreamHelper();
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/streaming.php' => config_path('streaming.php')
        ], 'laravel-redis-stream-config');

        $this->commands([
            StreamingCommand::class,
            MakeStreamChannelCommand::class,
        ]);
    }
}
