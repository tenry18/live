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
            'worker_num' => 1,
            'reload_async' => true,
            'max_wait_time'=>3,
            'enable_coroutine'=>true,
            'enable_static_handler'=>true,//静态文件处理
            'document_root' => EASYSWOOLE_ROOT.'/public', //静态文件目录 v4.4.0以下版本, 此处必须为绝对路径
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

