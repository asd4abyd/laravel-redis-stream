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
        $configPath = __DIR__ . '/../config/streaming.php';

        if (function_exists('config_path')) {
            $publishPath = config_path('streaming.php');
        } else {
            $publishPath = base_path('config/streaming.php');
        }

        if(file_exists($publishPath)){
            $configPath = $publishPath;
        }

        $this->mergeConfigFrom($configPath, 'streaming');

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
        if (function_exists('config_path')) {
            $publishPath = config_path('streaming.php');
        } else {
            $publishPath = base_path('config/streaming.php');
        }

        $this->publishes([
            __DIR__.'/../config/streaming.php' => $publishPath
        ], 'laravel-redis-stream-config');

        $this->commands([
            StreamingCommand::class,
            MakeStreamChannelCommand::class,
        ]);
    }
}
