<?php

$host='http://127.0.0.1:8080/srs';//不要瞎改
$debug="";
$rtmp_port='1935';
return [
    //srs 安装路径
    'srs_path'=>'/www/wwwroot/util/srs/srs2.0',
    'rtmp_port'=>$rtmp_port,
    //srs 配置文件
    'srs_config'=>[
        'listen'=>$rtmp_port.';',//监听端口 rtmp
        'max_connections'=>'1000;',//最大连接数
        'daemon'=>'off;',//后台启动 on|off
        'pid'=>TEMP_PATH.'/srs.pid'.';',//pid文件路径
        'ff_log_dir'=>TEMP_PATH.'/srs'.';',//日志目录。如果启用ffmpeg，每个转码流将创建一个日志文件。 /dev/null禁用日志。默认./objs。
        'srs_log_tank'=>'console;',//日志文件打印位置  console|file
        'srs_log_level'=>'error;',//日志级别 从高到低 verbose|info|trace|warn|error
        'srs_log_file'=>LOG_PATH.'/srs.log'.';',//日志文件位置
        'heartbeat'=>[//心跳
            'enabled'=>'off;',// on|off
            'interval'=>'3;',//心跳的间隔秒 0.3的倍数
            'url'=>$host.'/heartbeat'.$debug.';',//必须是一个restful的HTTP API URL, 数据:{"device_id": "my-srs-device","ip": "192.168.1.100"}
            'device_id'=>'master;',//这个设备的id
            'summaries'=>'off;'//是否有摘要报告 数据:{"summaries": summaries object.}
        ],
        'http_api'=>[//是否启用HTTP API
            'enabled'=>'off;',// on|off
            'listen'=>'1936;',//监听端口
            'crossdomain'=>'off;',//跨域请求  on|off
        ],
        //从其他协议到SRS的RTMP流。
        'stream_caster'=>[
            'enabled'=>'off;',// on|off
            //流类型
            #       mpegts_over_udp, MPEG-TS over UDP caster.
            #       rtsp, Real Time Streaming Protocol (RTSP).
            #       flv, FLV over HTTP POST.
            'caster'=>'rtsp;',
            'output'=>'rtmp://127.0.0.1/[app]/[stream];',
            /**
             * 对于MPEGTSUPROUDPCAST，请在UDP端口监听。例如，8935。
            对于RTSP连铸机，在TCP端口监听。例如，554。
            对于FLV连铸机，在TCP端口监听。例如，8936。
            支持：监听<[IP:]端口>
             */
            'listen'=>'554;',
            /** for the rtsp caster, the rtp server local port over udp,
            which reply the rtsp setup request message, the port will be used:
             */
            'rtp_port_min'=>'57200;',
            'rtp_port_max'=>'57300;',
        ],
        'vhost __defaultVhost__'=>[
            #'forward'=>'192.168.1.6:1935 192.168.1.7:1935;',//热备 转发到其他源站 (主) master
            #'mode'=>'remote',//边缘配置  origin为master 的地址
            #'origin'=>'192.168.1.81:1935',//
            //============================考虑GOP-Cache和累积延迟，推荐的低延时配置========================
            'gop_cache'=>'off;',//打开: 始终保留一个关键帧,客户端立即播放(延迟增大)  关闭:等待关键帧到来(等待期间黑屏) 如果需要最小延迟，则设置为off;如果需要客户快速启动，则设置为on。
            'queue_length'=>'8;',//累积延迟 配置直播队列的长度，服务器会将数据放在直播队列中，如果超过这个长度就清空到最后一个I帧：当然这个不能配置太小，譬如GOP是1秒，queue_length是1秒，这样会导致有1秒数据就清空，会导致跳跃。
            'min_latency'=>'on;',//最小延迟
            /*
             *是否启用MR(merged -read) 开启后 性能+，延迟+，和内存+，
             * 例如，延迟= 500ms,kbps = 3000kbps，每个发布连接都会消耗
             * 内存= 500 * 3000 / 8 = 187500B = 183KB
             * 当有2500个出版商时，SRS的总内存至少是: 183KB * 2500 = 446MB
             * 推荐300-2000;默认值:350 若需要低延迟配置，关闭merged-read，服务器每次收到1个包就会解析
             */
            'mr'=>['enabled'=>'off;'],
            /*
             * erged-Write，即一次发送N毫秒的包给客户端。这个算法可以将RTMP下行的效率提升5倍左右，SRS1.0每次writev一个packet支持2700客户端，SRS2.0一次writev多个packet支持10000客户端。
             * 用户可以配置merged-write一次写入的包的数目，建议不做修改：
             * 推荐300-1800;默认值350
             */
            'mw_latency'=>'100;',
            'tcp_nodelay'=>'on;',
            'http_hooks'=>[//http回调post请求  服务器必须返回HTTP代码200(Stauts OK) 和响应头 错误码是int型 0代表成功
                'enabled'=>'on;',
                'on_connect'=>$host.'/onConnect'.$debug.';',//客户端连接到指定的vhost和app时 {"action": "on_connect","client_id": 1985,"ip": "192.168.1.10", "vhost": "video.test.com", "app": "live","tcUrl": "rtmp://video.test.com/live?key=d2fa801d08e3f90ed1e1670e6e52651a", "pageUrl": "http://www.test.com/live.html"}
                'on_close'=>$host.'/onClose'.$debug.';',//关闭连接，或者SRS主动关闭连接 {"action": "on_close", "client_id": 1985,"ip": "192.168.1.10", "vhost": "video.test.com", "app": "live","send_bytes": 10240, "recv_bytes": 10240}
                'on_publish'=>$host.'/onPublish'.$debug.';',//当客户端发布流时  {"action": "on_publish","client_id": 1985,"ip": "192.168.1.10", "vhost": "video.test.com", "app": "live","stream": "livestream" }
                'on_unpublish'=>$host.'/onUnpublish'.$debug.';',//当客户端停止发布流时{"action": "on_unpublish","client_id": 1985,"ip": "192.168.1.10", "vhost": "video.test.com", "app": "live","stream": "livestream"}
                'on_play'=>$host.'/onPlay'.$debug.';',//当客户端开始播放流时{"action": "on_play","client_id": 1985,"ip": "192.168.1.10", "vhost": "video.test.com", "app": "live","stream": "livestream","pageUrl": "http://www.test.com/live.html"}
                'on_stop'=>$host.'/onStop'.$debug.';',//当客户端停止播放时 停止播放可能不会关闭连接，还能再继续播放。{"action": "on_stop","client_id": 1985,"ip": "192.168.1.10", "vhost": "video.test.com", "app": "live","stream": "livestream"}
                'on_dvr'=>$host.'/onDvr'.$debug.';',//当切片生成时{"action": "on_dvr","client_id": 1985,"ip": "192.168.1.10", "vhost": "video.test.com", "app": "live","stream": "livestream","cwd": "/usr/local/srs","file": "./objs/nginx/html/live/livestream.1420254068776.flv"}
                'on_hls'=>$host.'/onHls'.$debug.';',//{"action": "on_hls","client_id": 1985,"ip": "192.168.1.10", "vhost": "video.test.com", "app": "live","stream": "livestream","duration": 9.36, // in seconds"cwd": "/usr/local/srs","file": "./objs/nginx/html/live/livestream/2015-04-23/01/476584165.ts","url": "live/livestream/2015-04-23/01/476584165.ts","m3u8": "./objs/nginx/html/live/livestream/live.m3u8","m3u8_url": "live/livestream/live.m3u8","seq_no": 100}
                'on_hls_notify'=>$host.'/onHlsNotify'.$debug.';',//当切片生成时，回调这个url，使用GET回调
            ],
            'dvr'=>[//srs将流录制成flv文件，这个功能编译的时候要加–with-dvr选项
                'enabled'=>'off;',
                'dvr_path'=>'./objs/nginx/html/[app]/[stream].[timestamp].flv;',
                'dvr_plan'=>'session;',
                'dvr_duration'=>'30;',
                'dvr_wait_keyframe'=>'on;',
                'time_jitter'=>'full;',
            ],
            'hls'=>[//
                'enabled'=>'off;',// on|off
                /*单位秒，指定ts切片的最小长度。默认为10
                 * ts文件长度 = max(hls_fragment, gop_size) 如果ffmpeg中指定fps（帧速率）为20帧/秒，gop为200帧，那么gop_size=gop/fps=10秒
                 * 那么实际ts的长度为max(5,10) =10秒。这样实际ts切片的长度就与设定的不同了。
                 */
                'hls_fragment'=>'3;',
                /*倍数，控制m3u8的EXT-X-TARGETDURATION值，EXT-X-TARGETDURATION（整数）值标明了切片的最大时长。
                 * m3u8列表文件中EXTINF的值必须小于等于EXT-X-TARGETDURATION的值。
                 * EXT-X-TARGETDURATION在m3u8列表文件中必须出现一次。
                 */
                'hls_td_ratio'=>'1.5;',
                'hls_aof_ratio'=>'2.0;',//倍数。纯音频时，当ts时长超过配置的ls_fragment乘以这个系数时就切割文件。例如，当ls_fragment是10秒，hls_aof_ratio是2.0时，对于纯音频，10s*2.0=20秒时就切割ts文件。
                'hls_window'=>'15;',//单位：秒，指定HLS窗口大小，即m3u8中ts文件的时长之和，超过总时长后，丢弃第一个m3u8中的第一个切片，直到ts的总时长在这个配置项范围之内。即SRS保证：hls_window的值必须大于等于m3u8列表文件中所有ts切片时长的总和。
                'hls_on_error'=>'ignore;',// 错误策略 ignore：当错误发生时，忽略错误并停止输出hls（默认） disconnect：当发生错误时，断开推流连接 continue：当发生错误时，忽略错误并继续输出hls
                'hls_storage'=>'disk;',//存储方式 disk：把m3u8/ts写到磁盘 发送m3u8/ts到内存，但是必须使用srs自带的http server进行分发。  both， disk and ram。
                'hls_path'=>EASYSWOOLE_ROOT.'/public/hls'.';',//当hls写到磁盘时，指定写入的目录。
                'hls_m3u8_file'=>'[app]/[stream]/[stream].m3u8;',//生成hls的m3u8文件的文件名，有一些变量可用于生成m3u8文件的文件名：
                'hls_ts_file'=>'[app]/[stream]/[stream]-[seq].ts;',//m3u8文件的绝对路径为[SRS_Path]/objs/nginx/html/[app]/[stream].m3u8
                'hls_ts_floor'=>'off;',//是否使用floor的方式生成hls ts文件的路径。如实hls_ts_floor on; 使用timestamp/hls_fragment作为[timestamp]变量，即[timestamp]=timestamp/hls_fragment，并且使用enahanced算法生成下一个切片的差值。
                'hls_mount'=>'[vhost]/[app]/[stream].m3u8;',//内存HLS的M3u8/ts挂载点，和http_remux的mount含义一样
                'hls_acodec'=>'aac;',//默认的音频编码。当流的编码改变时，会更新PMT/PAT信息；默认是aac，因此默认的PMT/PAT信息是aac；如果流是mp3，那么可以配置这个参数为mp3，避免PMT/PAT改变。
                'hls_vcodec'=>'h264;',//默认的视频编码。当流的编码改变时，会更新PMT/PAT信息；默认是h264。如果是纯音频HLS，可以配置为vn，可以减少SRS检测纯音频的时间，直接进入纯音频模式。
                'hls_cleanup'=>'on;',//是否删除过期的ts切片，不在hls_window中就是过期。可以关闭清除ts切片，实现时移和存储，使用自己的切片管理系统。
                'hls_nb_notify'=>'64;',//从notify服务器读取数据的长度
                'hls_wait_keyframe'=>'on;',//是否按top切片，即等待到关键帧后开始切片。测试发现OS X和android上可以不用按go切片。
            ]
        ]
    ],
];