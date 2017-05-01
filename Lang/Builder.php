<?php

namespace PhpX\Lang;
use PhpX\Lang\Collection\Table;

abstract class Builder extends Table {
    public abstract function build ();
}