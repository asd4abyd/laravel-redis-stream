## Laravel Redis Stream

This package using to manage the Redis stream messages listener by firing handler classes based on the defined channels. in this package, you could assign multiple
classes to the same channel.

### Installation 

Require this package with composer using the following command:

```bash
composer require dweik/laravel-redis-stream
```

Then you need to publish the config files by executing the following command
```bash
php artisan vendor:publish --tag=laravel-redis-stream-config
```

You will find a new config file `config/streaming.php`, so there you have to set up the Redis connection under the **Redis** key.
then you need to define the names of the channels by assigning handler classes as below example:
- note: *We prefer use **phpredis** driver*.

```php
    'channels' => [
        'channel-name' => [
            App\Channels\SomeClassChannel::class
        ],
    ],
```

Also, you could define the trimming value for each channel under **trim** key.
Ex.,
```php
    'trim' => [
        'channel-name' => 1000, // it means keep 1000 messages at most
    ],
```
___

### Creating handler class

Use `artisan` command to make a new *handler class* by using the `make:channel-listener` function.
then you can find it on the `app/Channels` path

```bash
    php artisan make:channel-listener SomeClassChannel
```

### Run channel listener

To run *channel listener* for all channels use the following command
```bash
    php artisan stream:run
```

To run a specific channel you could pass to the `--channel` option the channel name like :_
```bash
    php artisan stream:run --channel=channel-name
```

### Send a message to Redis stream

There is a facade class that could handle the `xADD` command on redis

```php 
use LaravelStream\Redis\Facades\Stream;

class SomeClass {

    public function(){
    
        /*
        .
        . some code
        .        
        */
        
        
        /**
        *
        * @var string  $channel   channel name
        * @var mixed   $data      the message data
        * @var integer $trim      (default null) the channel messages size,
        *                         when using (null) value, this package will use 
        *                         the defined value on the config file "config/streaming.php".
        *                         when using (zero) value that means stop trimming function
        */
        
        $messageID = Stream::stream( $channel, $data, $trim);
        
        
    
    }

}

```
