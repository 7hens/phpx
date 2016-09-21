<?php

namespace PhpX\Net\Route;
use Exception;
use PhpX\Net\Http\HttpRequest;

class Router {
    protected $uri;
    protected $matcher;
    protected $invoker;
    protected $responder;

    /**
     * Router constructor.
     * @param IMatcher $matcher 路由解析器
     * @param IInvoker $invoker 执行器
     * @param IResponder $responder 响应器
     */
    public function __construct ($matcher, $invoker, $responder) {
        $this->uri = $this->getUri();
        $this->matcher = $matcher;
        $this->invoker = $invoker;
        $this->responder = $responder;
    }

    /**
     * 添加路由
     * @param string|array HTTP 请求方式
     * @param string $route 路由
     * @param string|callable $handler 处理器
     * @return Router
     */
    public function add ($httpMethod, $route, $handler) {
        try {
            if (in_array(HttpRequest::method(), (array) $httpMethod, true)) {
                $assocArgs = $this->matcher->match($route, $this->uri);
                if ($assocArgs !== null) {
                    $result = $this->invoker->invoke($handler, $assocArgs);
                    $this->responder->success($result);
                    return $this;
                }
            }
        } catch (Exception $e) {
            $this->responder->error($e);
        }
        return $this;
    }

    public function end () {
        $this->responder->otherwise();
        return $this;
    }

    public function resetUri ($uri) {
        $this->uri = $uri;
        return $this;
    }

    protected function getUri () {
        if (isset($_SERVER['PATH_INFO']))
            return $_SERVER['PATH_INFO'];
        if (count($_GET)) {
            return $this->getUriFromGet();
        }
        return '';
    }

    protected function getUriFromGet () {
        $uri = '';
        foreach ($_GET as $key => $value) {
            $uri .= $uri ? "&$key" : $key;
            if ($value) {
                $uri .= "=$value";
            }
        }
        return $uri;
    }
}