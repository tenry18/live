<?php
/**
 * 对外接口
 */

namespace App\Controller;


use App\Util\Stream;

class Api extends BC
{
    /**
     * 创建
     */
    public function create()
    {
        try{
            $data=$this->all();
            if(empty($data['stream'])||empty($data['live_host'])||empty($data['app'])||strlen($data['app'])>10){throw new \Exception('参数错误');}
            //判断是否存在
            if (Stream::exists(stream_key($data['app'],$data['stream']))) {throw new \Exception('该资源已存在');}
            Stream::set($data['stream'],$data['live_host'],$data['app']);
            $this->success();
        }catch (\Exception $exception){
            $this->error([],$exception->getMessage());
        }
    }

    /**
     * 更新
     */
    public function update()
    {
        try{
            $data=$this->all();
            if(empty($data['stream'])||empty($data['live_host'])||empty($data['app'])||strlen($data['app'])>10){throw new \Exception('参数错误');}
            //判断是否存在
            if (!Stream::exists(stream_key($data['app'],$data['stream']))) {throw new \Exception('该资源不存在');}
            Stream::set($data['stream'],$data['live_host'],$data['app']);
            $this->success();
        }catch (\Exception $exception){
            $this->error([],$exception->getMessage());
        }
    }

    /**
     * 删除
     */
    public function destroy()
    {
        try{
            $data=$this->all();
            if(empty($data['stream'])||empty($data['app'])){throw new \Exception('参数错误');}
            Stream::del(stream_key($data['app'],$data['stream']));
            $this->success();
        }catch (\Exception $exception){
            $this->error([],$exception->getMessage());
        }
    }
}