<?php echo '<?php' ?>


namespace App\Channels;

use LaravelStream\Redis\Interfaces\StreamChannel;

class <?php echo $name ?> implements StreamChannel
{

    public function handle($channelName, $id, $data)
    {
        // do something
    }
}
