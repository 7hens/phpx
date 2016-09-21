<?php

/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2016/8/25
 * Time: 10:43
 */
class Server {
    public static function phpSelf () {
        return $_SERVER['PHP_SELF'];
    }

    public static function argv () {
        return $_SERVER['argv'];
    }
}