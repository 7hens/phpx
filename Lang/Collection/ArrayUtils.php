<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2016/9/4
 * Time: 12:18
 */

namespace PhpX\Lang\Collection;


class ArrayUtils {
    /**
     * @param array $array
     * @param string $key
     * @param mixed $default
     * @return array|mixed
     */
    public static function lookUp ($array, $key = null, $default = null) {
        if ($key === null) return $array;

        return isset($array[$key]) ? $array[$key] : $default;
    }

    public static function join ($array, $glue) {
        return implode($glue, $array);
    }
    
}