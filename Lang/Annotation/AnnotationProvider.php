<?php

namespace PhpX\Lang\Annotation;
use PhpX\Lang\Collection\Bag;
use PhpX\Lang\IParser;

class AnnotationProvider {
    protected $parser;

    /**
     * AnnotationManager constructor.
     * @param IParser $parser
     */
    public function __construct ($parser) {
        $this->parser = $parser;
    }

    public static function getInstance ($parser = null) {
        return new self($parser ?: new DocCommentParser(new ValueParser()));
    }

    /**
     * @param \ReflectionFunctionAbstract|\ReflectionProperty|\ReflectionClass $reflector
     * @return array
     */
    public function getAnnotations ($reflector) {
        $text = $reflector->getDocComment();
        return $this->parser->parse($text);
    }
}