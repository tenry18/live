<?php
/**
 * ffmpeg进程
 */

namespace App\Process;


use App\Util\Stream;
use EasySwoole\Component\Process\AbstractProcess;
use EasySwoole\Component\TableManager;
use FFMpeg\FFMpeg;
use FFMpeg\Format\Video\X264;
use Swoole\Process;

class FFmpegProcess extends AbstractProcess
{

    /**音频码率因子
     * @var float
     */
    private static $audio_factor=0.6;
    /**视频码率因子
     * @var float
     */
    private static $video_factor=0.6;

    protected function run($arg)
    {
        //父进程进行信号监听
        Process::signal(SIGCHLD, function($sig) {Process::wait(false);});
    }

    /**开始推流
     * @param $stream_key
     * @return bool
     */
    private function pullStream(string $stream_key)
    {
        $row=Stream::get($stream_key);
        $ffmpegProcess=new Process(function (Process $process)use($row){
            $instance=\EasySwoole\EasySwoole\Config::getInstance();
            $ffmpeg = FFMpeg::create([
                'ffmpeg.binaries'  => $instance->getConf('srs.srs_path').'/objs/ffmpeg/bin/ffmpeg',
                'ffprobe.binaries' => $instance->getConf('srs.srs_path').'/objs/ffmpeg/bin/ffprobe',
                'timeout'=>'0',
            ]);

            $video = $ffmpeg->open($row['rtsp_host']);
            $video_format=$video->getFormat()->all();
            $video_info=$video->getStreams()->videos()->first()->all();
            $audio_info=$video->getStreams()->audios()->first()->all();

            $format=new X264();
            $format
                ->setKiloBitrate(1024)  //码率 比特率
                ->setAudioChannels($audio_info['channels'])   // 声道设置，1单声道，2双声道，3立体声
//                ->setAudioKiloBitrate($audio_bit_rate)//音频比特率
                ->setAudioCodec('libfdk_aac')
                ->setAdditionalParameters(['-vf','scale=-2:480','-f','flv']);//'-an'
            //此代码阻塞
            $instance=\EasySwoole\EasySwoole\Config::getInstance();
            $video->save($format, "rtmp://127.0.0.1:{$instance->getConf('srs.rtmp_port')}/".$row['app']."/".$row['stream_id']);
        },true,SOCK_STREAM,false);
        if (($phpProcessPid=$ffmpegProcess->start())===false){return false;}
        $streamTable=TableManager::getInstance()->get('stream');
        $streamTable->set($stream_key,['php_pid'=>$phpProcessPid]);
    }


    protected function onPipeReadable(\Swoole\Process $process)
    {
        /*
         * 该回调可选
         * 当有主进程对子进程发送消息的时候，会触发的回调，触发后，务必使用
         * $process->read()来读取消息
         */
        try{
            $stream_key=$this->getProcess()->read(32);
            //判断是否存在
            if(!Stream::exists($stream_key)){return false;}
            //避免重复调用
            $streamTable=TableManager::getInstance()->get('stream');
            if (!$streamTable->exist($stream_key)) {
                $this->pullStream($stream_key);
            }
        }catch (\Exception $exception){
            dump($exception->getMessage());
        }
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