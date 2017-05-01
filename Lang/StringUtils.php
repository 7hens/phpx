<?php

namespace PhpX\Lang;


class StringUtils {
    public static function split ($str, $delimiter, $limit = null) {
        return explode($delimiter, $str, $limit);
    } 
}