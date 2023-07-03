## Laravel Redis Stream

This package using to manage the Redis stream messages listener by firing handler classes based on the defined channels. in this package, you could assign multiple
classes to the same channel.

### Installation 

Require this package with composer using the following command:

```bash
composer require dweik/laravel-redis-stream
```

___
#### Laravel
Then you need to publish the config files by executing the following command
```bash
php artisan vendor:publish --tag=laravel-redis-stream-config
```

___
#### lumen
You need to add the follow line on `bootstrap/app.php` file:
```php
$app->register(LaravelStream\Redis\StreamServiceProvider::class);
```
and make sure that you loaded ***Redis*** by add the follow line _(if exists no need to add)_
```php
$app->register(Illuminate\Redis\RedisServiceProvider::class);
```
_**NOTE** if you need more information to how setup redis on lumen you can check the official website by a [click here](https://lumen.laravel.com/docs/master/cache)_   

Then you need to copy the streaming config file from `vendor/dweik/laravel-redis-stream/config/streaming.php` to
`config/streaming.php`  
___

On `config/streaming.php` file, you need to set up the Redis connection under the **redis** key.
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
