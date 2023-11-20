<?php

$client = new Swoole\Client(SWOOLE_SOCK_TCP);
if (!$client->connect("127.0.0.1", 9501)) {
    echo "connection failed";
    exit;
}

fwrite(STDOUT,"plz input message:");
$message = trim(fgets(STDIN));

//发送给server消息
$client->send($message);
//接收来自server的消息
$result = $client->recv();

echo $result;

