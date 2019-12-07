<?php


namespace App\Controller;


use EasySwoole\Http\AbstractInterface\Controller;

class BC extends Controller
{
    public function index()
    {
        $this->response()->withStatus(404);
        $file = EASYSWOOLE_ROOT.'/vendor/easyswoole/easyswoole/src/Resource/Http/404.html';
        if(!is_file($file)){
            $file = EASYSWOOLE_ROOT.'/src/Resource/Http/404.html';
        }
        $this->response()->write(file_get_contents($file));
    }

    protected function actionNotFound(?string $action)
    {
        $this->response()->withStatus(404);
        $file = EASYSWOOLE_ROOT.'/vendor/easyswoole/easyswoole/src/Resource/Http/404.html';
        if(!is_file($file)){
            $file = EASYSWOOLE_ROOT.'/src/Resource/Http/404.html';
        }
        $this->response()->write(file_get_contents($file));
    }

    /**获取一个参数
     * @param string $key
     * @return array|mixed
     */
    protected function input(string  $key)
    {
        return $this->request()->getRequestParam($key);
    }

    /**获取所有参数
     * @return array
     */
    protected function all()
    {
        return $this->request()->getRequestParam();
    }

    /**
     * @return string
     */
    protected function raw(){
        return $this->request()->getBody()->__toString();
    }

    /**成功
     * @param array $data
     * @param string $msg
     */
    protected function success(array $data=[],$msg='成功'){
        $this->response()->write(json_encode(['code'=>1,'msg'=>$msg,'data'=>$data]));
        $this->response()->end();
    }

    /**失败
     * @param array $data
     * @param string $msg
     */
    protected function error(array $data=[],$msg='失败'){
        $this->response()->write(json_encode(['code'=>0,'msg'=>$msg,'data'=>$data]));
        $this->response()->end();
    }
}