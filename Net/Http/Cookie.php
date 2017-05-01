<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2016/8/28
 * Time: 16:43
 */

namespace PhpX\Net\Http;
use PhpX\Lang\Collection\ArrayUtils;

class Cookie {
    const PERMANENT = '+1 year';
    private static $singleton = null;

    private $domain = '';
    private $path = '/';
    private $secure = false;
    private $prefix = 'mm_';

    private function __construct ($options = array()) {
        if (isset($options['path']) && (string) $options['path'])
            $this->path = (string) $options['path'];
        if (isset($options['prefix']))
            $this->prefix = (string) $options['prefix'];
        if (isset($options['domain']) && (string) $options['domain'])
            $this->domain = (string) $options['domain'];
        else {
            $this->domain = $_SERVER['SERVER_NAME'];
            $this->domain = '.' . preg_replace('#^www\.#', '', strtolower($this->domain));
        }
        $this->secure = (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on');
    }

    public static function getInstance ($options = array()) {
        if (!self::$singleton) {
            self::$singleton = new self($options);
        }
        return self::$singleton;
    }

    /**
     * Get current domain
     * @return string
     */
    public function getDomain() {
        return $this->domain;
    }

    /**
     * Get info about prefix setting
     * @return string
     */
    public function getPrefix() {
        return $this->prefix;
    }

    /**
     * Get info about path setting
     * @return string
     */
    public function getPath() {
        return $this->path;
    }
    /**
     * Get info about secure setting
     * @return boolean
     */
    public function isSecure() {
        return $this->secure;
    }

    /**
     * Get real name for cookie
     * @param string $name
     * @return string
     */
    public function getName ($name) {
        return $this->prefix . $name;
    }

    /**
     * Get a cookie
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function get ($name = null, $default = null) {
        return ArrayUtils::lookUp($_COOKIE, $this->getName($name), $default);
    }

    /**
     * Set a cookie
     * @param string $name The name of the cookie.
     * @param string $value The value of the cookie.
     * This value is stored on the clients computer;
     * do not store sensitive information.
     * Assuming the name is 'cookiename',
     * this value is retrieved through $_COOKIE['cookiename'] or Cookie::get('cookiename')
     * @param bool|string|int $expire The time the cookie expires.
     * Possible values:<br>
     * <strong>true</strong> - set cookie permanently<br>
     * <strong>false</strong> - set cookie for current session only<br>
     * <strong>int</strong> - The number of seconds to add to the current time<br>
     * <strong>string</strong> - Relative DateTime Formats (ex: '+3 days', '+1 week' etc.)<br>
     * See http://es.php.net/manual/en/datetime.formats.relative.php
     * @param bool $httpOnly When TRUE the cookie will be made accessible only through the HTTP protocol.
     * This means that the cookie won't be accessible by scripting languages, such as JavaScript.
     * It has been suggested that this setting can effectively help to reduce identity theft through XSS attacks
     * (although it is not supported by all browsers), but that claim is often disputed. Added in PHP 5.2.0. TRUE or FALSE
     * @return boolean If output exists prior to calling this function, setcookie() will fail and return FALSE.
     * If setcookie() successfully runs, it will return TRUE. This does not indicate whether the user accepted the cookie.
     */
    public function set ($name, $value, $expire = self::PERMANENT, $httpOnly = false)
    {
        if (empty($value)) // Unset cookie, hmm...
        {
            $expire = '-1 day';
        }
        $name = $this->getName($name);
        $value = (string) $value;
        if ($expire === true) {
            $expire = self::PERMANENT;
        } else if (is_numeric($expire)) {
            $expire = time() + $expire;
        } elseif (is_string($expire) && $expire) {
            $expire = strtotime($expire);
        } else {
            $expire = 0;
        }
        if (time() > $expire) {
            $value = '';
            unset($_COOKIE[$name]);
        } else {
            $_COOKIE[$name] = $value;
        }
        if (headers_sent()) {
            return false;
        }
        return setcookie($name, $value, $expire, $this->path, $this->domain, $this->secure, $httpOnly);
    }

    public function remove ($name) {
        return $this->set($name, '', '-1 day');
    }
}