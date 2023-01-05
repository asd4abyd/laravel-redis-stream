<?php

namespace LaravelStream\Redis\Commands;

use Illuminate\Console\Command;
use Illuminate\Redis\Connections\Connection;
use Illuminate\Redis\RedisManager;
use Log;
use Throwable;

class StreamingCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stream:run {--channel= : channel name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'run streaming listener';
    private $prefix;
    private $redis;
    /** @var boolean */
    private $supportMsgPack;
    private $channels;

    private $logSuccess;
    private $logSuccessChannel;
    private $logErrors;
    private $logErrorChannel;


    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        // loading config values
        $this->prefix = config('streaming.prefix', '');
        $this->supportMsgPack = config('streaming.support_msgpack', false);
        $this->logSuccess = config('streaming.log_success', false);
        $this->logSuccessChannel = config('streaming.log_success_channel');
        $this->logErrors = config('streaming.log_errors', true);
        $this->logErrorChannel = config('streaming.log_error_channel');
        $this->channels = config('streaming.channels', []);
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->redis = $this->getRedis();

        $blockTimeout = config('streaming.block_timeout', null);

        if (count($this->channels) == 0) {
            $this->warn('no channels defined on config file.');
            return 1;
        }

        $listeners = [];

        $selectedChannel = $this->option('channel');

        foreach ($this->channels as $channelName => $handler) {
            if (!is_null($selectedChannel) and $channelName != $selectedChannel) {
                continue;
            }
            $listeners[$channelName] = $this->getKey($channelName, '0');
        }

        if (!count($listeners) && !is_null($selectedChannel)) {
            $this->warn('the selected channel is not exists.');
            return 1;
        }

        while (true) {
            $channelsWithMessages = $this->getMessages($listeners, $blockTimeout);
            if (count($channelsWithMessages)) {
                foreach ($channelsWithMessages as $channelName => $messages) {
                    $channelName = $this->fixChannelName($channelName);

                    if (isset($this->channels[$channelName])) {
                        foreach ($messages as $messageNumber => $payload) {
                            $listeners[$channelName] = $messageNumber;

                            $this->firePayload($this->channels[$channelName], $channelName, $messageNumber, $payload);
                        }
                    }


                    if (count($messages)) {
                        $this->setKey($channelName, $listeners[$channelName]);
                    }
                }
            }
        }

        return 0;
    }

    private function firePayload(array $handlers, $channelName, $messageNumber, $payload)
    {
        foreach ($handlers as $handler) {
            $data = $this->handleData($payload);
            try {

                (new $handler)->handle($channelName, $messageNumber, $data);
                $this->info_log($channelName, $messageNumber, $data);
            }
            catch (Throwable $exception) {
                $this->error_log($exception, $channelName, $messageNumber, $data);
            }
        }
    }

    private function handleData($data)
    {
        if (isset($data['data'])) {
            $data = $data['data'];
        }
        else {
            return [];
        }

        if ($this->supportMsgPack and function_exists('msgpack_unpack')) {
            return msgpack_unpack($data);
        }

        return json_decode($data, 1) ?: $data;
    }

    public function info_log($channelName, $messageNumber, $data)
    {
        $this->info("message consumed - channel: [{$channelName}]  message ID: [{$messageNumber}]");

        if ($this->logSuccess) {
            Log::channel($this->logSuccessChannel)
                ->info("message consumed - channel: [{$channelName}]  message ID: [{$messageNumber}]", [
                    "Channel Name" => $channelName,
                    "Message Number" => $messageNumber,
                    "Data" => $data
                ]);
        }
    }

    public function error_log(Throwable $exception, $channelName, $messageNumber, $data)
    {
        $this->error("error: fail consume - channel: [{$channelName}]  message ID: [{$messageNumber}]");

        if ($this->logErrors) {
            Log::channel($this->logErrorChannel)
                ->info("message consumed - channel: [{$channelName}]  message ID: [{$messageNumber}]", [
                    "Channel Name" => $channelName,
                    "Message Number" => $messageNumber,
                    "Data" => $data,
                    "Exception Message" => $exception->getMessage(),
                    "Exception Trace" => "\n".$exception->getTraceAsString(),
                ]);
        }
    }

    public function fixChannelName($channelName)
    {
        $prefix = $this->redis->client()->_prefix('');
        $prefix = preg_quote($prefix);
        return preg_replace('/^' . $prefix . '/', '', $channelName);
    }

    public function getRedis(): Connection
    {
        $config = config('database.redis');
        $connectionName = config('streaming.redis.connection');
        $driveName = config('streaming.redis.drive', 'phpredis');

        $redisManager = new  RedisManager(app(), $driveName, $config);

        return $redisManager->connection($connectionName);
    }

    public function getMessages($listeners, $blockTimeout)
    {
        return $this->redis->xRead($listeners, null, $blockTimeout);
    }

    public function setKey($channelName, $id)
    {
        $this->redis->set($this->prefix . $channelName, $id);
    }

    public function getKey($channelName, $default = null)
    {
        $value = $this->redis->get($this->prefix . $channelName);

        return $value ?? $default;
    }
}
