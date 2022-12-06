<?php


namespace LaravelStream\Redis\Helpers;


use Illuminate\Redis\Connections\Connection;
use Illuminate\Redis\RedisManager;

class StreamHelper
{
    private $redis;
    private $trim;
    private $supportMsgPack;

    public function __construct()
    {
        $this->redis = $this->getRedis();
        $this->trim = config('streaming.trim', []);
        $this->supportMsgPack = config('streaming.support_msgpack', false);
    }

    public function stream($channel, $data, $trim = null)
    {
        if (is_null($trim)) {
            $trim = $this->getTrim($channel);
        }

        $data = $this->prepareMessage($data);

        $this->redis->xADD($channel, '*', ['data' => $data], $trim);

    }

    public function prepareMessage($data)
    {
        if ($this->supportMsgPack and function_exists('msgpack_pack')) {
            return msgpack_pack($data);
        }

        return json_encode($data);
    }

    public function getTrim($channelName)
    {
        return $this->trim[$channelName] ?? 0;
    }


    public function getRedis(): Connection
    {
        $config = config('database.redis');
        $connectionName = config('streaming.connection');
        $driveName = config('streaming.drive', 'phpredis');

        $redisManager = new  RedisManager(app(), $driveName, $config);

        return $redisManager->connection($connectionName);
    }
}
