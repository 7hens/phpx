<?php

namespace PhpX\Net\Route;
use Closure;
use PhpX\Net\Json;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionParameter;

interface IInvoker {
    /**
     * 执行相关的处理器，其参数为关联数组
     * @param callable $handler 处理器（可以为函数、方法、闭包）
     * @param array $arguments 参数，是一个关联数组
     * @return mixed
     */
    public function invoke ($handler, $arguments);
}