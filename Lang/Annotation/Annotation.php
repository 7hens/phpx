<?php

namespace PhpX\Lang\Annotation;


class Annotation {
    protected static $annotationProvider;

    protected static function getProvider () {
        if (self::$annotationProvider === null) {
            self::$annotationProvider = AnnotationProvider::getInstance();
        }
        return self::$annotationProvider;
    }

    public static function getAnnotations ($reflector) {
        return self::getProvider()->getAnnotations($reflector);
    }

    /**
     * @param string $class
     * @return array
     */
    public static function getClassAnnotations ($class) {
        return self::getAnnotations(new \ReflectionClass($class));
    }

    public static function getPropertyAnnotations ($class, $prop = null) {
        if ($prop === null) {
            list($class, $prop) = explode('::', $class);
        }
        return self::getAnnotations(new \ReflectionProperty($class, $prop));
    }

    public static function getMethodAnnotations ($class, $method = null) {
        if ($method === null) {
            list($class, $method) = explode('::', $class);
        }
        return self::getAnnotations(new \ReflectionMethod($class, $method));
    }

    public static function getFunctionAnnotations ($func) {
        return self::getAnnotations(new \ReflectionFunction($func));
    }
}