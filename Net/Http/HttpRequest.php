<?php

namespace PhpX\Net\Http;
use PhpX\Lang\Collection\ArrayUtils;
use PhpX\Net\Json;

class HttpRequest {

    const METHOD_HEAD = 'HEAD';
    const METHOD_GET = 'GET';
    const METHOD_POST = 'POST';
    const METHOD_PUT = 'PUT';
    const METHOD_PATCH = 'PATCH';
    const METHOD_DELETE = 'DELETE';
    const METHOD_OPTIONS = 'OPTIONS';
    const METHOD_OVERRIDE = '_METHOD';

    const OVERRIDE = 'HTTP_X_HTTP_METHOD_OVERRIDE';

    private static $dataOfBody = null;

    private static $phpInputContent = null;
    private static $phpInputArray = null;
    private static $phpSubmitted = null;

    private static $resolvers = null;

    protected static $formats = array(
        'html' => array('text/html', 'application/xhtml+xml'),
        'txt'  => array('text/plain'),
        'js'   => array('application/javascript', 'application/x-javascript', 'text/javascript'),
        'css'  => array('text/css'),
        'json' => array('application/json', 'application/x-json'),
        'xml'  => array('text/xml', 'application/xml', 'application/x-xml'),
        'rdf'  => array('application/rdf+xml'),
        'atom' => array('application/atom+xml'),
        'rss'  => array('application/rss+xml'),
    );

    protected static function isOverridden() {
        return isset($_POST[self::OVERRIDE]) || isset($_SERVER[self::OVERRIDE]);
    }

    protected static function getPhpInputContent () {
        return self::$phpInputContent ?: file_get_contents('php://input');
    }

    protected static function getPhpInputArray () {
        if (self::$phpInputArray === null) {
            parse_str(self::getPhpInputContent(), self::$phpInputArray);
        }
        return self::$phpInputArray;
    }

    protected static function getPutOrDelete ($key = NULL, $default = NULL) {
        $array = self::isOverridden() ? $_POST : self::getPhpInputArray();
        return ArrayUtils::lookUp($array, $key, $default);
    }

    public static function method() {
        $method = self::isOverridden()
            ? (isset($_POST[self::OVERRIDE])
                ? $_POST[self::OVERRIDE]
                : $_SERVER[self::OVERRIDE])
            : $_SERVER['REQUEST_METHOD'];
        return strtoupper($method);
    }

    public static function body ($default = NULL) {
        return file_get_contents('php://input') ?: $default;
    }

    public static function get ($key = NULL, $default = NULL) {
        return ArrayUtils::lookUp($_GET, $key, $default);
    }

    public static function post ($key = NULL, $default = NULL) {
        return ArrayUtils::lookUp($_POST, $key, $default);
    }

    public static function data ($key = NULL, $default = NULL) {
        if (self::$dataOfBody === null) {
            self::$dataOfBody = Json::decode(self::body());
        }
        return ArrayUtils::lookUp(self::$dataOfBody, $key, $default);
    }

    public static function put($key = NULL, $default = NULL) {
        return self::method() === self::METHOD_PUT
            ? self::getPutOrDelete($key, $default)
            : $default;
    }

    public static function delete($key = NULL, $default = NULL) {
        return self::method() === self::METHOD_DELETE
            ? self::getPutOrDelete($key, $default)
            : $default;
    }

    public static function files($key = NULL, $default = NULL) {
        return ArrayUtils::lookUp($_FILES, $key, $default);
    }

    public static function session($key = NULL, $default = NULL) {
        return ArrayUtils::lookUp($_SESSION, $key, $default);
    }

    public static function cookie($key = NULL, $default = NULL) {
        return ArrayUtils::lookUp($_COOKIE, $key, $default);
    }

    public static function env($key = NULL, $default = NULL) {
        return ArrayUtils::lookUp($_ENV, $key, $default);
    }

    public static function server($key = NULL, $default = NULL) {
        return ArrayUtils::lookUp($_SERVER, $key, $default);
    }

    public static function input($key = NULL, $default = NULL) {
        if (self::$phpSubmitted === null) {
            self::$phpSubmitted = (array) $_GET + (array) $_POST + self::getPhpInputArray();
        }
        return ArrayUtils::lookUp(self::$phpSubmitted, $key, $default);
    }

    public static function all ($key = NULL, $default = NULL) {
        $all = array_merge(self::input(), self::files());
        return ArrayUtils::lookUp($all, $key, $default);
    }

    public static function protocol ($default = 'HTTP/1.1') {
        return self::server('SERVER_PROTOCOL', $default);
    }

    public static function scheme ($decorated = false) {
        $scheme = self::secure() ? 'https' : 'http';
        return $decorated ? "$scheme://" : $scheme;
    }

    public static function secure() {
        if (strtoupper(self::server('HTTPS')) == 'ON')
            return TRUE;
        if (!self::entrusted()) return FALSE;
        return (strtoupper(self::server('SSL_HTTPS')) == 'ON' ||
            strtoupper(self::server('X_FORWARDED_PROTO')) == 'HTTPS');
    }

    public static function safe() {
        return in_array(static::method(), array(self::METHOD_GET, self::METHOD_HEAD));
    }

    public static function ajax() {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH'])
            ? strtoupper($_SERVER['HTTP_X_REQUESTED_WITH']) == 'XMLHTTPREQUEST'
            : FALSE;
    }

    public static function referrer($default = NULL) {
        return self::server('HTTP_REFERRER', $default);
    }

    public static function resolvers($resolvers = array()) {
        if ($resolvers || empty(self::$resolvers)) {
            self::$resolvers = $resolvers + array(
                    'PATH_INFO',
                    'REQUEST_URI' => function($uri) {
                        return parse_url($uri, PHP_URL_PATH);
                    },
                    'PHP_SELF',
                    'REDIRECT_URL'
                );
        }
        return self::$resolvers;
    }

    public static function url() {
        return self::scheme(true) . self::host() . self::port(TRUE)
            . self::uri() . self::query(true);
    }

    public static function uri() {
        foreach (self::resolvers() as $key => $resolver) {
            $key = is_numeric($key) ? $resolver : $key;
            if (isset($_SERVER[$key])) {
                if (is_callable($resolver)) {
                    $uri = $resolver($_SERVER[$key]);
                    if ($uri !== FALSE) return $uri;
                } else {
                    return $_SERVER[$key];
                }
            }
        }
        return '';
    }

    public static function query ($decorated = false) {
        if (count((array) $_GET)) {
            $query = http_build_query($_GET);
            return $decorated ? "?$query" : $query;
        }
        return '';
    }

    public static function segments($default = array()) {
        return explode('/', trim(self::uri() ?: $default, '/'));
    }

    public static function segment($index, $default = NULL) {
        $segments = explode('/', trim(self::uri() ?: '', '/'));
        if ($index < 0) {
            $index *= -1;
            $segments = array_reverse($segments);
        }
        return ArrayUtils::lookUp($segments, $index - 1, $default);
    }
}