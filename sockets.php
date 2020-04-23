<?php

require_once('vendor/autoload.php');
//require_once "index.php";

$socket = new \HemiFrame\Lib\WebSocket\WebSocket("0.0.0.0", 3333);
//$socket->on("connect", function(\HemiFrame\Lib\WebSocket\Client $client) {});

$socket->on("receive", function ($client, $data) use ($socket) {
    foreach ($socket->getClients() as $item) {
        file_put_contents('sock.txt',$item);
        //if ($item->id == $client->getId()) {
        $vp = new Andchir\VideoProcessing([
            'melt_path' => '/usr/bin/melt'
        ]);
        $percentage = intval($vp->getRenderingPercent('/var/www/html/uploads/tmp/'.$data));
        $socket->sendData($item, "Hello");
        //}
    }
});
echo "Server started at port 3333";
$socket->startServer();