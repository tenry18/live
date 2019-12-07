<?php
/**
 * 流文件管理
 */
namespace App\Util;


use EasySwoole\Component\TableManager;

class Stream
{
    private static $path=EASYSWOOLE_ROOT.DIRECTORY_SEPARATOR.'storage/stream/';

    /**获取
     * @param string $stream_key
     * @return mixed|array|null
     */
    public static function get(string $stream_key)
    {
        if (self::exists($stream_key)) {
           return json_decode(file_get_contents(self::$path.$stream_key),true);
        }
        return null;
    }

    /**设置
     * @param string $stream_id
     * @param string $rtspHost
     * @param string $app
     */
    public static function set(string $stream_id,string $rtspHost,string $app)
    {
        $f = fopen(self::$path.stream_key($app,$stream_id), "w");
        fwrite($f,json_encode([
            'stream_id'=>$stream_id,
            'rtsp_host'=>$rtspHost,
            'app'=>$app,
        ]));
        fclose($f);
    }

    /**删除
     * @param string $stream_key
     * @return bool
     */
    public static function del(string $stream_key)
    {
        if (self::exists($stream_key)) {
            return unlink(self::$path.$stream_key);
        }
        return false;
    }


    /**判断是否存在
     * @param string $stream_key
     * @return bool
     */
    public static function exists(string $stream_key)
    {
        return file_exists(self::$path.$stream_key);
    }


    /**结束指定流
     * @param string $stream_key
     */
    public static function stop(string $stream_key)
    {
        $streamTable=TableManager::getInstance()->get('stream');
        $watchTable=TableManager::getInstance()->get('watch');
        if (!$streamTable->exist($stream_key)) {return;}
        $php_pid=$streamTable->get($stream_key,'php_pid');
        //检测php进程
        if (\swoole_process::kill($php_pid, 0)) {
            //根据父进程号结束所有子进程
            exec("pkill -P {$php_pid}");
            //清空用户(还需要踢掉用户)
            $watchTable->del($stream_key);
        }
        $streamTable->del($stream_key);
    }
}