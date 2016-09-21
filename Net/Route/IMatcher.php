<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2016/9/3
 * Time: 20:44
 */

namespace PhpX\Net\Route;

interface IMatcher {
    /**
     * 匹配路由
     * <br>如 <b><code>/foo/1/bar/2</code></b>
     * 在匹配路由 <b><code>/foo/{id}[/bar/{num:\d+}]</code></b> 时，会被解析成
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
    public function match ($route, $uri);
}