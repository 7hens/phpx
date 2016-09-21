<?php

namespace PhpX\Lang\Collection;
use ArrayIterator;
use Closure;
use Countable;
use IteratorAggregate;
use JsonSerializable;

class Table implements IteratorAggregate, Countable, JsonSerializable {
    protected $data;

    public function __construct ($data = array()) {
        $this->reset($data);
    }

    public function keys () {
        return array_keys($this->data);
    }

    public function values () {
        return array_values($this->data);
    }

    public function reset ($data = array()) {
        $this->data = (array) $data;
        return $this;
    }

    public function add ($data = array()) {
        foreach ($data as $key => $value) {
            $this->set($key, $value);
        }
        return $this;
    }

    public function get ($key, $default = null) {
        return ArrayUtils::lookUp($this->data, $key, $default);
    }

    public function set ($key, $value) {
        $this->data[$key] = $value;
        return $this;
    }

    public function removeKey ($key) {
        $value = $this->get($key);
        unset($this->data[$key]);
        return $value;
    }


    public function hasKey ($key) {
        return array_key_exists($key, $this->data);
    }

    public function hasValue ($value) {
        return in_array($value, $this->values());
    }

    public function filter ($filter) {
        $result = array();
        if ($filter instanceof Closure) {
            foreach ($this->data as $key => $value) {
                if ($filter($key, $value)) {
                    $result[$key] = $value;
                }
            }
//            $result = array_filter($this->data, $filter);
        } else {
            $keys = is_array($filter) ? $filter : func_get_args();
            foreach ($keys as $key) {
                $result[$key] = $this->get($key);
            }
        }
        return $result;
    }

    public function all () {
        return $this->data;
    }

    public function count() {
        return count($this->data);
    }

    public function getIterator() {
        return new ArrayIterator($this->data);
    }

    function jsonSerialize() {
        return $this->data;
    }
}