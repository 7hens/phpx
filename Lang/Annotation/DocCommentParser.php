<?php

namespace PhpX\Lang\Annotation;
use PhpX\Lang\IParser;

class DocCommentParser implements IParser {
    const ANNOTATION_PATTERN = '@([A-Za-z_][0-9A-Za-z_\\.]*)[ \t]*([^@\\n]*)\\s*';

    protected $valueParser;

    /**
     * DocCommentParser constructor.
     * @param IParser $valueParser
     */
    public function __construct ($valueParser = null) {
        $this->valueParser = $valueParser;
    }

    /**
     * 解析文本
     * @param string $text 要解析的文本
     * @return array 返回数组
     */
    public function parse ($text) {
        $result = array();
        preg_match_all('#'.self::ANNOTATION_PATTERN.'#s', $text, $matches);
        foreach ($matches[2] as $key => $value) {
            $key = $matches[1][$key];
            $result[$key] = $this->valueParser->parse($value);
        }
        return $result;
    }
}