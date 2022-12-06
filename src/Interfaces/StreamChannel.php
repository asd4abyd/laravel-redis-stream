<?php


namespace LaravelStream\Redis\Interfaces;


Interface StreamChannel
{

    public function handle($channelName, $id, $data);

}
