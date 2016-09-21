<?php

namespace PhpX\Net\Route;

class YaMatcher implements IMatcher {
    const REGEX_VALUE = '[^/]+';
    const REGEX_VARIABLE = '\\{\\s*([A-Za-z_][0-9A-Za-z_]*)\\s*\\}';

    public function match ($route, $uri) {
        $segments = preg_split('#'.static::REGEX_VARIABLE.'#', $route, -1, PREG_SPLIT_DELIM_CAPTURE);

        $parameters = array();
        $pattern = '#^';
        foreach ($segments as $key => $value) {
            if ($key % 2) {
                $parameters[] = $value;
                $pattern .= '('.self::REGEX_VALUE.')';
            } else {
                $pattern .= $value;
            }
        }
        $pattern .= '$#';

        $assocArgs = array();
        if (preg_match_all($pattern, $uri, $matches)) {
            foreach ($parameters as $key => $value) {
                $assocArgs[$value] = $matches[$key + 1][0];
            }
            return $assocArgs;
        }
        return null;
    }
}