<?php

namespace PhpX\Lang\Collection;

class Bag extends Table {
    public static function getInstance ($data = array()) {
        return new self($data);
    }
}