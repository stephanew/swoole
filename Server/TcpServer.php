<?php
$serv = new Swoole\Server("127.0.0.1", 9501, SWOOLE_BASE);
// $serv = new Swoole\Server("0.0.0.0", 9501);

$serv->set([
    'worker_num' => 6,//worker进程 CPU的1-4倍 普通模式
    'max_request' => 100,
    //'reactor_nume' => 3, reactor num 线程
]);

//fd客户端连接唯一标识  reactor_id 线程id
$serv->on('connect', function ($serv, $fd, $reactor_id){
    //echo "[#".posix_getpid()."]\tClient@[$fd:$reactor_id]: Connect.\n";
    echo "Client: {$reactor_id} - {$fd}, -Connect.\n";
});


$serv->on('receive', function (Swoole\Server $serv, $fd, $reactor_id, $data) {
    echo "[#".$serv->worker_id."]\tClient[$reactor_id]-[$fd] receive data: $data\n";
    if ($serv->send($fd, "Server {$reactor_id} - {$fd} - {$data}\n") == false) {
        echo "error\n";
    }

});

$serv->on('close', function ($serv, $fd, $reactor_id) {
    echo "[#".posix_getpid()."]\tClient@[$fd:$reactor_id]: Close.\n";
});

$serv->start();
//ps aft |grep tcp.php linux下查看开启多少个进程
//mac只能会用ps aux
