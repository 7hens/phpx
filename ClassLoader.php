<?php

namespace PhpX;

class ClassLoader {
    /**
     * 加载某个类
     * @param string $className 类名
     * @param string $folder 根目录
     */
    public static function loadClass ($className, $folder = 'src') {
        $separator = \DIRECTORY_SEPARATOR;
        $filePath = "{$_SERVER['DOCUMENT_ROOT']}{$separator}{$folder}{$separator}{$className}.php";
        $filePath = str_replace('\\', $separator, $filePath);
        if (file_exists($filePath)) {
            include $filePath;
        }
    }

    /**
     * 将给定的函数注册为<b>类的自动加载函数</b>
     * @param string|callback $funcName 要注册的函数
     */
    public static function register ($funcName = __CLASS__ . '::loadClass') {
        spl_autoload_register($funcName);
    }

    /**
     * 将给定的<b>类的自动加载函数</b>取消注册
     * @param string|callback $funcName 要取消注册的函数
     */
    public static function unregister ($funcName = __CLASS__ . '::loadClass') {
        spl_autoload_register($funcName);
    }
}