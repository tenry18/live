## PHP跨平台直播

支持:  
* 按需拉流,RTSP转RTMP,HLS  
* 客户端主动推流,封装多码率  
* 同步录制  
* 多级集群  

更多支持请参阅  
 https://github.com/ossrs/srs/wiki/v2_CN_Home  

###一.基本信息:  


1.入门推荐书籍:  
* [FFmpeg从入门到精通](https://book.douban.com/subject/30178432/)
* [Swoole从入门到精通](https://wiki.swoole.com/wiki/page/1.html)
* [SRS概述 必读!!!!!!!!!!!!!!](https://github.com/ossrs/srs/wiki/v2_CN_Home)

2.环境  
    
    php          => 7.1.0
    swoole       => 4.4
    easyswoole   => 3.3


3.使用:

    1.  composer install
    2.  php easyswoole install_srs
    3.  php easwswoole start    
    
###一.HTTP API接口  

***  
1.添加设备

    POST  http://服务器ip:服务端口/api/create 
    {
    	"stream": "唯一名称",
    	"live_host": "rtsp://账号:密码@摄像头ip:摄像头端口/stream1",//不同设备rtsp地址可能不一样
    	"app": "live"
    }
    
2.编辑设备
    
    POST  http://服务器ip:服务端口/api/update 
    {
        "stream": "唯一名称",
        "live_host": "rtsp://账号:密码@摄像头ip:摄像头端口/stream1",//不同设备rtsp地址可能不一样
        "app": "live"
    }
    
2.删除设备
    
    POST  http://服务器ip:服务端口/api/destroy 
    {
        "stream": "唯一名称",
        "app": "live"
    }
    
    
###二.观看  

***  
    rtmp://服务器ip:1935/{app}/{stream}