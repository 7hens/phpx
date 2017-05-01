<?php

namespace PhpX\Net\Http;
use PhpX\Net\Json;

class HttpResponse {
    public static function json ($data) {
        header("Content-type: application/json");
        echo Json::encode($data, JSON_UNESCAPED_UNICODE);
        $responder = null;
        exit;
    }

    public static function plain ($data) {
        header("Content-type: text/plain");
        echo $data;
        exit;
    }

    public static function html ($data) {
        header("Content-type: text/html");
        if (self::isHtmlOrPhp($data)) {
            self::includeDocument($data);
        } else {
            echo $data;
        }
        exit;
    }

    public static function includeDocument ($filePath) {
        $separator = \DIRECTORY_SEPARATOR;
        $filePath = ltrim($filePath, '/\\');
        $filePath = $_SERVER['DOCUMENT_ROOT'] . $separator. $filePath;
        $filePath = str_replace(array('\\', '/'), $separator, $filePath);
        return include $filePath;
    }

    private static function isHtmlOrPhp ($url) {
        $pattern = '/.*?\\.(php|html|htm)/i';
        return preg_match($pattern, $url);
    }
}