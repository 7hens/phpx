<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2016/9/4
 * Time: 20:36
 */

namespace PhpX\Lang;


interface IParser {
    /**
     * 解析文本
     * @param string $text 要解析的文本
     * @return mixed 返回解析后的数据
     */
    public function parse ($text);
}