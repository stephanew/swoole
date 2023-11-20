<?php

//单进程管理process
//主进程已退出 子进程还在运行
/*for ($i = 0; $i < 2; $i++) {
    $process = new Swoole\Process(function() {
        $t = rand(10,20);

        echo "Child process: #" . getmypid() . "start and sleep $t second" . PHP_EOL;

        sleep($t);
        echo "Child process #" . getmypid() . " exit" . PHP_EOL;
    });

    $process->start();
}*/


//创建 3 个子进程，主进程用 wait 回收进程
//主进程异常退出时，子进程会继续执行，完成所有任务后退出  kill主进程 子进程也会继续执行完才会退出
for ($n = 1; $n <= 3; $n++) {
    $process = new Swoole\Process(function () use ($n) {
        echo 'Child #' . getmypid() . " start and sleep {$n}s" . PHP_EOL;
        sleep($n);

        //$t = rand(20,30);
        //echo 'Child #' . getmypid() . " start and sleep {$t}s" . PHP_EOL;
        //sleep($t);


        echo 'Child #' . getmypid() . ' exit' . PHP_EOL;
    });
    $process->start();
}

/*for ($n = 3; $n--;) {
    $status = Swoole\Process::wait(true);
    echo "Recycled #{$status['pid']}, code={$status['code']}, signal={$status['signal']}" . PHP_EOL;
}
echo 'Parent #' . getmypid() . ' exit' . PHP_EOL;*/
//while (1) sleep(50);


//每个子进程结束后，父进程必须都要执行一次 wait() 进行回收，否则子进程会变成僵尸进程，会浪费操作系统的进程资源。
//如果父进程有其他任务要做，没法阻塞 wait 在那里，父进程必须注册信号 SIGCHLD 对退出的进程执行 wait。
//SIGCHILD 信号发生时可能同时有多个子进程退出；必须将 wait() 设置为非阻塞，循环执行 wait 直到返回 false。

Swoole\Process::signal(SIGCHLD, function ($sig) {
    //必须为false，非阻塞模式
    while ($ret = Swoole\Process::wait(false)) {
        echo "PID={$ret['pid']}\n";
    }
});

echo "Parent: #" . getmypid(). "exit ", PHP_EOL;

Swoole\Timer::tick(2000, function(){});