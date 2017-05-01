<?php

namespace PhpX\Net\Route;


use Exception;

interface IResponder {
    /**
     * 在路由成功的情况下，做出相关的响应
     * @param mixed $data 要处理的数据
     */
    public function success ($data);

    /**
     * 在出现异常的情况下，做出相关的响应
     * @param Exception $e 捕获到的异常
     */
    public function error ($e);

    /**
     * 在路由匹配失败的情况下，做出相关的响应
     */
    public function otherwise ();
}