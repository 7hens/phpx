<?php

namespace PhpX\Lang\Annotation;
use Exception;
use PhpX\Lang\IParser;
use PhpX\Net\Json;

class ValueParser implements IParser {

    /**
     * 解析文本
     * @param string $text 要解析的文本
     * @return mixed 返回解析后的数据
     */
    public function parse($text) {
        try {
            $result = Json::decode($text);
        } catch (Exception $e) {
            $float = filter_var($text, FILTER_VALIDATE_FLOAT);
            $result = ($float !== false) ? $float : $text;
        }
        return $result;
    }

    protected function decode ($value) {
        if (defined('JSON_PARSER_NOTSTRICT')) {
            return json_decode($value, true, 512, JSON_PARSER_NOTSTRICT);
        }
        return json_decode($value, true);
    }
}