<?php

return [

    'redis' => [
        'connection' => 'stream',
        'drive' => 'phpredis',
    ],
    'prefix' => 'key_',

    /**********************************************************
     **********************************************************
     ** logging the messages on defined channel              **
     ** use (null) for using the default channel             **
     **********************************************************/
    'log_success' => false,
    'log_success_channel' => null,
    'log_errors' => true,
    'log_error_channel' => null,


    /**********************************************************
     **********************************************************
     ** msgpack is a good package using for compress data    **
     ** php extinction https://pecl.php.net/package/msgpack  **
     ** official website https://msgpack.org/                **
     **********************************************************/
    'support_msgpack' => true,


    /**********************************************************
     **********************************************************
     ** it using in XADD command for block the call          **
     ** blocking_timeout => integer                          **
     ** set value in millisecond                             **
     **********************************************************
     ** using 0 (zero) for blocking until received a message **
     ** using null for avoid using block option              **
     **********************************************************/
    'block_timeout' => 1000,


    /**********************************************************
     **********************************************************
     ** for defining the queue messages length by selecting  **
     ** the channel, use (zero) to stop trimming             **
     **********************************************************
     ** for example :-                                       **
     ** 'trim' => [                                          **
     **     'channel-name' => integer,                       **
     ** ]                                                    **
     **********************************************************/
    'trim' => [
        // channels
    ],


    /**********************************************************
     **********************************************************
     ** for defining the channels and pointing               **
     ** the handle classes                                   **
     ** set value in millisecond                             **
     **********************************************************
     ** for example :-                                       **
     ** 'channels' => [                                      **
     **     'channel-name' => [                              **
     **         list of handling Classes                     **
     **     ],                                               **
     ** ]                                                    **
     **********************************************************/
    'channels' => [
        // channels
    ]
];
