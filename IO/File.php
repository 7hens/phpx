<?php

namespace PhpX\IO;

class File {
    public $name;
    public $tmpName;
    public $type;
    public $size;
    public $error;

    public function __construct ($fileName) {
        $this->name = $fileName;
    }

    public static function getInstance ($fileName) {
        return new self($fileName);
    }

    public function getExtension () {
        return strtolower(pathinfo($this->name, PATHINFO_EXTENSION));
    }

    public function getBaseName () {
        return pathinfo($this->name, PATHINFO_BASENAME);
    }

    public function hasError () {
        return $this->error != UPLOAD_ERR_OK;
    }

    public function saveAs ($path) {
        return copy($this->tmpName, $path);
    }
}