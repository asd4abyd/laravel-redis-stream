<?php

namespace LaravelStream\Redis\Facades;

use Illuminate\Support\Facades\Facade;

/**
 *
 * @see \Illuminate\Redis\RedisManager
 * @see \Illuminate\Contracts\Redis\Factory
 */
class Stream extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'stream';
    }
}

