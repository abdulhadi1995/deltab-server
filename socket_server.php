<?php

require_once('vendor/autoload.php');
//require_once "index.php";

$socket = new \HemiFrame\Lib\WebSocket\WebSocket("0.0.0.0", 3333);
$socket->on("connect", function(\HemiFrame\Lib\WebSocket\Client $client) use ($socket) {
    echo "\n".$client->getId()." Connected";
    $socket->sendData($client,json_encode([
        'type'=> 'connection',
        'socketID' => $client->getId()
    ]),$masked=false);
});

$socket->on("disconnect", function(\HemiFrame\Lib\WebSocket\Client $client, $statusCode, $reason) use ($socket) {
    echo "\n".$client->getId()." Connected";
    $socket->sendData($client,json_encode([
        'type'=> 'disconnection',
        'socketID' => $client->getId()
    ]),$masked=false);
});

$socket->on("receive", function ($client, $data) use ($socket) {
    if(!empty($data)){
        $_data = unserialize($data);
        $clientSocketID = $_data['frontEndUserID'];
        foreach ($socket->getClients() as $item) {
            if($item->getId() == $clientSocketID){
                echo "\nSending To ".$clientSocketID."\n";
                $socket->sendData($item, json_encode([
                    "type" => "percentage",
                    "value" => $_data['percentage']
                ]),$masked = false);
            }
        }
    }

    /*if(sizeof($_data) > 0){
        $clientSocketID = $_data['frontEndUserID'];
        foreach ($socket->getClients() as $item) {
            if($item->id == $clientSocketID){
                $socket->sendData($item, "Percentage".$_data['percentage'],$masked = false);
            }
        }
    }*/

    //foreach ($socket->getClients() as $item) {

        //print_r($data);
        /*if($item->id == $client->getId() ){
            echo "\nRequest Received From:". $client->getId();
            $socket->sendData($item, "Percentage",$masked = false);
        }*/

        /*//if ($item->id == $client->getId()) {
        $vp = new Andchir\VideoProcessing([
            'melt_path' => '/usr/bin/melt'
        ]);
        $percentage = intval($vp->getRenderingPercent('/var/www/html/uploads/tmp/'.$data));*/
        //$socket->sendData($item, "Percentage".$percentage,$masked = false);
        //}
    //}
});

echo "Server started at port 3333";
$socket->startServer();