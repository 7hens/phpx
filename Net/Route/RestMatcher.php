<?php

namespace PhpX\Net\Route;

class RestMatcher implements IMatcher {
    const REGEX_VALUE = '[^/]+';
    const REGEX_VARIABLE = <<<'REGEX'
\{
    ([^\{\}]*?)
    ([A-Za-z_][0-9A-Za-z_]*) \s*
    (?:
        : \s
        (
            [^\{\}]*
            (?:
                \{ (?-1) \}
                [^\{\}]*
            )*
        )
    )?
    ()
\}
REGEX;

    /**
     * 匹配路由
     * <br>如 <b><code>/foo/1/bar/2</code></b>
     * 在匹配路由 <b><code>/foo/{id}{/bar/num: \d+}</code></b> 时，会被解析成
     * <code><pre>
     * [
     *      "id"   => "1"
     *      "num"  => "2"
     * ]
     * </pre></code>
     * @param string $route
     * @param string $uri
     * @return array
     */
    public function match($route, $uri) {
        $segments = preg_split('#'.static::REGEX_VARIABLE.'#x', $route, -1, PREG_SPLIT_DELIM_CAPTURE);

        $parameters = array();
        $pattern = '#^';
        $segmentLength = count($segments);
        for ($i = 0; $i < $segmentLength - 5; $i += 5) {
            $static = $segments[$i];
            $optional = $segments[$i + 1];
            $param = $segments[$i + 2];
            $paramPattern = $segments[$i + 3] ?: self::REGEX_VALUE;
            $paramPattern = '('.$paramPattern.')';
            $pattern .= $static.(
                ($optional)
                    ? '(?:'.$optional.$paramPattern.')?'
                    : $paramPattern
                );
            $parameters[] = $param;
        }
        $pattern .= $segments[$segmentLength - 1].'$#x';

        if (preg_match_all($pattern, $uri, $matches)) {
            $assocArgs = array();
            foreach ($parameters as $key => $value) {
                if ($argValue = $matches[$key + 1][0]) {
                    $assocArgs[$value] = $argValue;
                }
            }
            return $assocArgs;
        }
        return null;
    }
}