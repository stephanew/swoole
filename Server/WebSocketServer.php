<?php

$websocket = new Swoole\WebSocket\Client("127.0.0.1", 9504);

//监听打开事件
$websocket->on('Open', function ($websocket, $request) {
    while(1) {
        $time = date('Y-m-d H:i:s');
        $websocket->push($request->fd, "hello, welcome $time\n");
        Swoole\coroutine::sleep(10);//普通的sleep无法在swoole里使用 子进程里才能使用
    }
});

//监听消息时间
$websocket->on('Message', function ($websocket, $frame){
    echo "Message: $frame->data\n";
    $websocket->push($frame->fd, "Server: $frame->data\n");
});

$websocket->on('Close', function ($websocket, $fd) {
    echo "Client-$fd is closed";
});

$websocket->start();
