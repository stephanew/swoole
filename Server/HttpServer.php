<?php

//$http = new Swoole\Http\Server('127.0.0.1', 9504);
//$http = new Swoole\Http\Server('127.0.0.1', 9503, SWOOLE_BASE);//1个主进程2个worker进程
$http = new Swoole\Http\Server('127.0.0.1', 9503, SWOOLE_PROCESS);

$http->set([
    'worker_num' => 2,
]);

$http->on('Request', function ($request, $response) {//监听request时间 回调函数
    echo "接收到了请求", PHP_EOL;//命令行输出
    $response->header('Content-Type', 'text/html; charset=utf8');
    $response->end('<H1>hello swoole #' . rand(1000,9999) . '</H1>');//页面输出
});

echo "启动服务", PHP_EOL;
$http->start();
//http://localhost:9503/ 访问



//异步任务
//这里是异步任务的工作进程数和之前普通的的work num不一样
/*$http->set([
    'task_worker_num' => 4  //但是实际会有6个 第一个是主进程 底下会开启一个管理进程负责管理worker进程 如果有进程挂了它会负责再拉起来 最后4个work进程
]);

$http->on('Request', function ($request, $response) use ($http) {//监听request时间 回调函数
    echo "接收到了请求", PHP_EOL;//命令行输出
    $response->header('Content-Type', 'text/html; charset=utf8');
    $http->task("发送邮件");
    $http->task("发送广播");
    $http->task("发送队列");

    $http->task("发送邮件2");
    $http->task("发送广播2");
    $http->task("发送队列2");
    //典型的会先处理完当前的4个task再接后续的任务 多进程

    $response->end('<H1>hello swoole #' . rand(1000,9999) . '</H1>');//页面输出
});

$http->on('Task', function ($serv, $task_id, $reactor_id, $data)//reactor 线程
{
    $sleepSec = rand(1,5);
    echo "new Asynctask[id = $task_id] sleep $sleepSec" . PHP_EOL;
    sleep($sleepSec);

    $serv->finish("$data -> ok");
});


$http->on('Finish', function ($serve, $task_id, $data) {
    echo "Asynctask[id = $task_id] finish: $data" . PHP_EOL;
});

echo "服务启动", PHP_EOL;
$http->start();*/


