<?php

//use Swoole\Redis\Server;

define('DB_FILE', __DIR__ . '/db');

$server = new Swoole\Redis\Server("127.0.0.1", 9505, SWOOLE_BASE);//redis-cli -p 9505访问 127.0.0.1:9505  与6379不相关

if (is_file(DB_FILE)) {
    $server->data = unserialize(file_get_contents(DB_FILE));
} else {
    $server->data = array();
}

//所有的命令都需要在setHandler里面定义 一个小型的redis 基于redis协议的服务器
$server->setHandler('GET', function ($fd, $data) use ($server) {//setHandler相当于监听 GET为redis里面的GET
    if (count($data) == 0) {
        return $server->send($fd, Swoole\Redis\Server::format(Swoole\Redis\Server::ERROR, "ERR wrong number of arguments for 'GET' command"));
    }

    $key = $data[0];
    if (empty($server->data[$key])) {
        return $server->send($fd, Swoole\Redis\Server::format(Swoole\Redis\Server::NIL));
    } else {
        return $server->send($fd, Swoole\Redis\Server::format(Swoole\Redis\Server::STRING, $server->data[$key]));
    }
});

$server->setHandler('SET', function ($fd, $data) use ($server) {
    if (count($data) < 2) {
        return $server->send($fd, Swoole\Redis\Server::format(Swoole\Redis\Server::ERROR, "ERR wrong number of arguments for 'SET' command"));
    }

    $key = $data[0];
    $server->data[$key] = $data[1];
    return $server->send($fd, Swoole\Redis\Server::format(Swoole\Redis\Server::STATUS, "OK"));
});

$server->setHandler('sAdd', function ($fd, $data) use ($server) {
    if (count($data) < 2) {
        return $server->send($fd, Swoole\Redis\Server::format(Swoole\Redis\Server::ERROR, "ERR wrong number of arguments for 'sAdd' command"));
    }

    $key = $data[0];
    if (!isset($server->data[$key])) {
        $array[$key] = array();
    }

    $count = 0;
    for ($i = 1; $i < count($data); $i++) {
        $value = $data[$i];
        if (!isset($server->data[$key][$value])) {
            $server->data[$key][$value] = 1;
            $count++;
        }
    }

    return $server->send($fd, Swoole\Redis\Server::format(Swoole\Redis\Server::INT, $count));
});

$server->setHandler('sMembers', function ($fd, $data) use ($server) {
    if (count($data) < 1) {
        return $server->send($fd, Swoole\Redis\Server::format(Swoole\Redis\Server::ERROR, "ERR wrong number of arguments for 'sMembers' command"));
    }
    $key = $data[0];
    if (!isset($server->data[$key])) {
        return $server->send($fd, Swoole\Redis\Server::format(Swoole\Redis\Server::NIL));
    }
    return $server->send($fd, Swoole\Redis\Server::format(Swoole\Redis\Server::SET, array_keys($server->data[$key])));
});

$server->setHandler('hSet', function ($fd, $data) use ($server) {
    if (count($data) < 3) {
        return $server->send($fd, Swoole\Redis\Server::format(Swoole\Redis\Server::ERROR, "ERR wrong number of arguments for 'hSet' command"));
    }

    $key = $data[0];
    if (!isset($server->data[$key])) {
        $array[$key] = array();
    }
    $field = $data[1];
    $value = $data[2];
    $count = !isset($server->data[$key][$field]) ? 1 : 0;
    $server->data[$key][$field] = $value;
    return $server->send($fd, Swoole\Redis\Server::format(Swoole\Redis\Server::INT, $count));
});

$server->setHandler('hGetAll', function ($fd, $data) use ($server) {
    if (count($data) < 1) {
        return $server->send($fd, Swoole\Redis\Server::format(Swoole\Redis\Server::ERROR, "ERR wrong number of arguments for 'hGetAll' command"));
    }
    $key = $data[0];
    if (!isset($server->data[$key])) {
        return $server->send($fd, Swoole\Redis\Server::format(Swoole\Redis\Server::NIL));
    }
    return $server->send($fd, Swoole\Redis\Server::format(Swoole\Redis\Server::MAP, $server->data[$key]));
});

$server->on('WorkerStart', function ($server) {
    Swoole\Timer::tick(10000, function () use ($server) {//tick:定时器 每隔100000毫秒执行一个回调函数 持久化功能 写到磁盘文件上
        file_put_contents(DB_FILE, serialize($server->data));
    });
    /*$server->tick(10000, function () use ($server) {//tick:定时器 每隔100000毫秒执行一个回调函数 持久化功能 写到磁盘文件上
        file_put_contents(DB_FILE, serialize($server->data));
    });*/
});

$server->start();

