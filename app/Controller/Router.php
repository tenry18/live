<?php


namespace App\Controller;


use EasySwoole\Http\AbstractInterface\AbstractRouter;
use FastRoute\RouteCollector;
use EasySwoole\Http\Request;
use EasySwoole\Http\Response;

class Router extends AbstractRouter
{
    function initialize(RouteCollector $routeCollector)
    {
        $this->addApi($routeCollector);
        $this->addSrs($routeCollector);
        $this->setGlobalMode(true);
        $this->setMethodNotAllowCallBack(function (Request $request,Response $response){
            $response->withStatus(403);
            return false;//结束此次响应
        });
        $this->setRouterNotFoundCallBack(function (Request $request,Response $response){
            $response->withStatus(404);
            return false;//结束此次响应
        });
    }

    private function addSrs(RouteCollector $routeCollector)
    {
        $routeCollector->addGroup('/srs',function (RouteCollector $routeCollector){
            $routeCollector->addRoute('POST', '/onConnect','/Srs/onConnect');
            $routeCollector->addRoute('POST', '/onClose','/Srs/onClose');
            $routeCollector->addRoute('POST', '/onPublish','/Srs/onPublish');
            $routeCollector->addRoute('POST', '/onUnpublish','/Srs/onUnpublish');
            $routeCollector->addRoute('POST', '/onPlay','/Srs/onPlay');
            $routeCollector->addRoute('POST', '/onStop','/Srs/onStop');
            $routeCollector->addRoute('POST', '/onDvr','/Srs/onDvr');
            $routeCollector->addRoute('POST', '/onHls','/Srs/onHls');
            $routeCollector->addRoute('POST', '/onHlsNotify','/Srs/onHlsNotify');
            $routeCollector->addRoute('POST', '/heartbeat','/Srs/heartbeat');
        });
    }

    private function addApi(RouteCollector$routeCollector)
    {
        $routeCollector->addGroup('/api',function (RouteCollector $routeCollector){
            $routeCollector->addRoute('POST', '/create','/Api/create');
            $routeCollector->addRoute('POST', '/update','/Api/update');
            $routeCollector->addRoute('POST', '/destroy','/Api/destroy');
        });
    }
}