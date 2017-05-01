<?php

namespace PhpX\Net\Route;

use Closure;
use PhpX\Lang\Reflection\FunctionCaller;

class YaInvoker extends FunctionCaller implements IInvoker {
    protected $namespace;

    public function __construct ($namespace = '') {
        $this->namespace = $namespace;
    }

    public function invoke ($handler, $arguments) {
        switch (true) {
            case ($func = $handler) instanceof Closure:
            case function_exists($func = $this->namespace.'\\'.$handler):
                return $this->callFunction($func, $arguments);
            case is_callable($func):
                return $this->callMethod($func, $arguments);
            case self::canBeIncludedByPhp($handler):
                self::includeDocument($handler);
                break;
            default:
                throw new BadRouteException("invalid Handler");
        }
        return null;
    }

    public static function canBeIncludedByPhp ($url) {
        $pattern = '/.*?\\.(php|html|htm)/i';
        return preg_match($pattern, $url);
    }

    public static function includeDocument ($filePath) {
        $separator = \DIRECTORY_SEPARATOR;
        $filePath = ltrim($filePath, '/\\');
        $filePath = $_SERVER['DOCUMENT_ROOT'] . $separator. $filePath;
        $filePath = str_replace(array('\\', '/'), $separator, $filePath);
        return include $filePath;
    }
}