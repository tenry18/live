<?php
/**
 * 用于处理srs回调
 */

namespace App\Controller;



use App\Util\Stream;
use EasySwoole\Component\TableManager;
use EasySwoole\EasySwoole\ServerManager;

class Srs extends BC
{
    /**
     * 心跳
     */
    public function heartbeat()
    {
        $this->retSrs();
    }

    /**
     * 连接
     */
    public function onConnect()
    {
        try{
            dump(__FUNCTION__);
//            $data=json_decode($this->raw(),true);
            $this->retSrs();
        }catch (\Exception $exception){
            $this->retSrs(SRS_ERROR);
        }
    }

    /**
     * 关闭连接 不仅仅是播放端,还有推流端
     * {"action": "on_close", "client_id": 1985,"ip": "192.168.1.10", "vhost": "video.test.com", "app": "live","send_bytes": 10240, "recv_bytes": 10240}
     */
    public function onClose()
    {
        try{
            dump(__FUNCTION__);
            $data=json_decode($this->raw(),true);
            if(empty($data)){throw new \Exception();}
            $watchTable=TableManager::getInstance()->get('watch');
            $clientTable=TableManager::getInstance()->get('client');
            //判断是否是播放端
            if (!$clientTable->exist($data['client_id'])) {throw new \Exception();}

            //获取客户端对应流
            $clientStreamKey=$clientTable->get($data['client_id'],'stream_key');
            //判断流下客户端
            $watchClient=$watchTable->get($clientStreamKey,'rows');
            $watchClient=$watchClient?json_decode($watchClient,true):[];
            //删除客户端对应流
            $clientTable->del($data['client_id']);

            //删除流下客户端(当前连接)
            foreach ($watchClient as $key=>$client_id){if ($client_id==$data['client_id']) {unset($watchClient[$key]);}}

            if(empty($watchClient)){
                //结束进程
                Stream::stop($clientStreamKey);
            }else{
                $watchTable->set($clientStreamKey,['rows'=>json_encode($watchClient)]);
            }
            $this->retSrs();
        }catch (\Exception $exception){
            $this->retSrs();//不能阻止关闭
        }
    }

    /**
     * 发布流
     * {"action": "on_publish","client_id": 1985,"ip": "192.168.1.10", "vhost": "video.test.com", "app": "live","stream": "livestream" }
     */
    public function onPublish()
    {
        try{
            dump(__FUNCTION__);
            $data=json_decode($this->raw(),true);
            if(empty($data)){throw new \Exception();}
            $this->retSrs();
        }catch (\Exception $exception){
            $this->retSrs(SRS_ERROR);
        }
    }

    /**
     * 停止发布流
     * {"action": "on_unpublish","client_id": 1985,"ip": "192.168.1.10", "vhost": "video.test.com", "app": "live","stream": "livestream"}
     */
    public function onUnpublish()
    {
        try{
            dump(__FUNCTION__);
            $data=json_decode($this->raw(),true);
            if(empty($data)||!Stream::exists(stream_key($data['app'],$data['stream']))){throw new \Exception();}
            Stream::stop(stream_key($data['app'],$data['stream']));
            $this->retSrs();
        }catch (\Exception $exception){
            $this->retSrs(SRS_ERROR);
        }
    }

    /**
     * 开始播放
     *{"action": "on_play","client_id": 1985,"ip": "192.168.1.10", "vhost": "video.test.com", "app": "live","stream": "livestream","pageUrl": "http://www.test.com/live.html"}
     */
    public function onPlay()
    {
        try{
            dump(__FUNCTION__);
            $data=json_decode($this->raw(),true);
            if(empty($data)||!Stream::exists(stream_key($data['app'],$data['stream']))){throw new \Exception();}

            //发送到 自定义ffmpeg进程
            if (ServerManager::getInstance()->getProcess('ffmpeg')->write(stream_key($data['app'],$data['stream']))===false) {throw new \Exception();}

            $clientTable=TableManager::getInstance()->get('client');
            $watchTable=TableManager::getInstance()->get('watch');

            //记录客户端对应流
            $clientTable->set($data['client_id'],['stream_key'=>stream_key($data['app'],$data['stream'])]);

            //记录流下客户端
            $watchClient=$watchTable->get(stream_key($data['app'],$data['stream']),'rows');
            $watchClient=$watchClient?json_decode($watchClient,true):[];
            array_push($watchClient,$data['client_id']);
            $watchTable->set(stream_key($data['app'],$data['stream']),['rows'=>json_encode($watchClient)]);

            $this->retSrs();
        }catch (\Exception $exception){
            $this->retSrs(SRS_ERROR);
        }
    }

    /**
     * 当客户端停止播放时。备注：停止播放可能不会关闭连接，还能再继续播放。
     */
    public function onStop()
    {
        try{
            dump(__FUNCTION__);
//            $data=json_decode($this->raw(),true);
            $this->retSrs();
        }catch (\Exception $exception){
            $this->retSrs(SRS_ERROR);
        }
    }

    public function onDvr()
    {
        try{
            dump(__FUNCTION__);
//            $data=json_decode($this->raw(),true);
            $this->retSrs();
        }catch (\Exception $exception){
            $this->retSrs(SRS_ERROR);
        }
    }

    public function onHls()
    {
        try{
            dump(__FUNCTION__);

//            $data=json_decode($this->raw(),true);
            $this->retSrs();
        }catch (\Exception $exception){
            $this->retSrs(SRS_ERROR);
        }
    }

    public function onHlsNotify()
    {
        try{
            dump(__FUNCTION__);

//            $data=json_decode($this->raw(),true);
             $this->retSrs();
        }catch (\Exception $exception){
            $this->retSrs(SRS_ERROR);
        }
    }

    /**发送srs响应
     * @param int $code
     */
    private function retSrs(int $code=SRS_SUCCESS)
    {
        $this->response()->write($code);
        $this->response()->withStatus(200);
        $this->response()->end();
    }
}