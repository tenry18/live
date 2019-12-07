<?php


namespace App\Process;


use EasySwoole\Component\Process\AbstractProcess;

class SrsProcess  extends AbstractProcess
{
    private $srsTempConfPath=TEMP_PATH.DIRECTORY_SEPARATOR.'srs.conf';

    protected function run($arg)
    {
        $instance=\EasySwoole\EasySwoole\Config::getInstance();
        $config=$instance->getConf('srs');

        try{
            $this->loadSrsConfig($config['srs_path'],$config['srs_config']);
            $this->start($config['srs_path']);
        }catch (\Exception $exception){
            dump($exception->getMessage());
        }

    }


    /**加载配置文件
     * @param string $srsBasePath
     * @param array $srsConf
     * @return bool
     * @throws \Exception
     */
    private function loadSrsConfig(string $srsBasePath,array $srsConf)
    {
        //检测二进制文件
        if (!file_exists($srsBasePath.'/objs/srs')) {throw new \Exception('没有找到srs可执行程序');}
        if(empty($srsConf)){echo "srs配置信息加载失败\n\n";return false;}
        $srsConf=json_encode($srsConf);
        $srsConf=substr($srsConf, 1);
        $srsConf=substr($srsConf, 0, -1);
        $srsConf=str_replace("\",\"","\n",$srsConf);//替换掉每个属性的逗号
        $srsConf=str_replace("\":\"",' ',$srsConf);
        $srsConf=str_replace("\"",'',$srsConf);
        $srsConf=str_replace("\\",'',$srsConf);//url等的http:\/
        $srsConf=str_replace(",",'',$srsConf);
        $srsConf=str_replace(":{"," { \n",$srsConf);//键值对的 冒号
        $srsConf=str_replace(";}"," ;} \n",$srsConf);//键值对的 冒号
        $myConf = fopen($this->srsTempConfPath, "w");
        fwrite($myConf,$srsConf);
        fclose($myConf);
    }


    /**启动服务
     * @param string $srsBasePath
     */
    private function start(string $srsBasePath)
    {
        $this->getProcess()->exec($srsBasePath."/objs/srs", ['-c',$this->srsTempConfPath]);
    }

    protected function onPipeReadable(\Swoole\Process $process)
    {
        /*
         * 该回调可选
         * 当有主进程对子进程发送消息的时候，会触发的回调，触发后，务必使用
         * $process->read()来读取消息
         */
    }

    protected function onShutDown()
    {
        /*
         * 该回调可选
         * 当该进程退出的时候，会执行该回调
         */
    }

    protected function onException(\Throwable $throwable, ...$args)
    {
        /*
         * 该回调可选
         * 当该进程出现异常的时候，会执行该回调
         */
    }
}