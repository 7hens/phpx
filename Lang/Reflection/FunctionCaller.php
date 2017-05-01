<?php

namespace PhpX\Lang\Reflection;
use BadFunctionCallException;
use Closure;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionParameter;

class FunctionCaller {
    private static $singleton;

    private function __construct() {
    }

    public static function getInstance () {
        if (self::$singleton == null) {
            self::$singleton = new self();
        }
        return self::$singleton;
    }

    public function call ($func, $assocArgs, $object = null) {
        if (!is_callable($func)) {
            throw new BadFunctionCallException("Not a function");
        }
        switch (true) {
            case $func instanceof Closure:
            case function_exists($func):
                return $this->callFunction($func, $assocArgs);
            default:
                return $this->callMethod($func, $assocArgs, $object);
        }
    }

    /**
     * @param ReflectionParameter[] $params
     * @param array $assocArgs
     * @return array
     */
    protected function getOrderedArgs ($params, $assocArgs) {
        $orderedArgs = array();
        foreach ($params as $i) {
            $name = $i->getName();
            $position = $i->getPosition();
            $orderedArgs[$position] = $assocArgs[$name];
        }
        return $orderedArgs;
    }

    protected function callFunction ($func, $assocArgs) {
        if (!$func instanceof ReflectionFunction) {
            $func = new ReflectionFunction($func);
        }
        $params = $func->getParameters();
        $orderedArgs = $this->getOrderedArgs($params, $assocArgs);
        return $func->invokeArgs($orderedArgs);
    }

    protected function callMethod ($method, $assocArgs, $object = null) {
        if (!$method instanceof ReflectionMethod) {
            $method = new ReflectionMethod($method);
        }
        $params = $method->getParameters();
        $orderedArgs = $this->getOrderedArgs($params, $assocArgs);
        return $method->invokeArgs($object, $orderedArgs);
    }
}