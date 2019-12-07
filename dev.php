<?php
return [
    'SERVER_NAME' => "EasySwoole",
    'MAIN_SERVER' => [
        'LISTEN_ADDRESS' => '0.0.0.0',
        'PORT' => 8080,
        'SERVER_TYPE' => EASYSWOOLE_WEB_SERVER, //可选为 EASYSWOOLE_SERVER  EASYSWOOLE_WEB_SERVER EASYSWOOLE_WEB_SOCKET_SERVER,EASYSWOOLE_REDIS_SERVER
        'SOCK_TYPE' => SWOOLE_TCP,
        'RUN_MODEL' => SWOOLE_PROCESS,
        'SETTING' => [
            'worker_num' => swoole_cpu_num(),
            'reload_async' => true,
            'max_wait_time'=>3,
            'enable_coroutine'=>true
        ],
        'TASK'=>[
            'workerNum'=>0,
            'maxRunningNum'=>128,
            'timeout'=>15
        ]
    ],
    'TEMP_DIR' => __DIR__.DIRECTORY_SEPARATOR.'storage'.DIRECTORY_SEPARATOR.'temp',
    'LOG_DIR' => __DIR__.DIRECTORY_SEPARATOR.'storage'.DIRECTORY_SEPARATOR.'log',
];

