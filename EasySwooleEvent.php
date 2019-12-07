<?php
namespace EasySwoole\EasySwoole;


use App\Process\FFmpegProcess;
use App\Process\SrsProcess;
use EasySwoole\Component\Di;
use EasySwoole\Component\TableManager;
use EasySwoole\EasySwoole\Swoole\EventRegister;
use EasySwoole\EasySwoole\AbstractInterface\Event;
use EasySwoole\Http\Request;
use EasySwoole\Http\Response;
use Swoole\Table;

class EasySwooleEvent implements Event
{

    public static function initialize()
    {
        Di::getInstance()->set(SysConst::HTTP_CONTROLLER_NAMESPACE,'App\\Controller\\');//配置控制器命名空间
        $instance = \EasySwoole\EasySwoole\Config::getInstance();
        defined('TEMP_PATH') or define('TEMP_PATH',$instance->getConf('TEMP_DIR'));
        defined('LOG_PATH') or define('LOG_PATH',$instance->getConf('LOG_DIR'));

        defined('SRS_ERROR') or define('SRS_ERROR',1);
        defined('SRS_SUCCESS') or define('SRS_SUCCESS',0);

        date_default_timezone_set('Asia/Shanghai');
    }

    public static function mainServerCreate(EventRegister $register)
    {
        self::loadConfig();
        self::initSwooleTable();
        self::initProcess();
        self::initTimer();
    }

    /**
     * 加载自定义配置文件
     */
    public static function loadConfig()
    {
        $instance = \EasySwoole\EasySwoole\Config::getInstance();
        foreach (glob(EASYSWOOLE_ROOT.DIRECTORY_SEPARATOR.'config/*.php') as $filePath){
            $instance->setConf(rtrim(basename($filePath),'.php'),require_once $filePath);
        }
    }

    /**
     * 加载内存表
     */
    public static function initSwooleTable()
    {
        //记录流对应进程号 key=stream_key
        TableManager::getInstance()->add('stream', ['php_pid'=>['type'=>Table::TYPE_INT,'size'=>11],], 1024);
        //记录流下客户端(实现自动结束流) key=stream_key
        TableManager::getInstance()->add('watch', ['rows'=>['type'=>Table::TYPE_STRING,'size'=>4096]], 1024);
        //记录客户端对应流(实现自动结束流) key=client_id
        TableManager::getInstance()->add('client', ['stream_key'=>['type'=>Table::TYPE_STRING,'size'=>32]], 1024);
    }

    /**
     * 初始化自定义进程
     */
    public static function initProcess()
    {
        dump(getmypid());
        /**srs进程**/
        ServerManager::getInstance()->addProcess(new SrsProcess(),'srs');
        /**ffmpeg进程**/
        $processConfig = new \EasySwoole\Component\Process\Config();
        $processConfig->setProcessName('ffmpeg');
        $processConfig->setPipeType(SOCK_STREAM);//DGRAM出现丢包,问题仅存在于internet网络的UDP通信
        $processConfig->setEnableCoroutine(true);
        ServerManager::getInstance()->addProcess(new FFmpegProcess($processConfig),'ffmpeg');
    }


    /**
     * 初始化定时器
     */
    public static function initTimer()
    {

    }

    public static function onRequest(Request $request, Response $response): bool
    {
        return true;
    }

    public static function afterRequest(Request $request, Response $response): void
    {
        // TODO: Implement afterAction() method.
    }
}