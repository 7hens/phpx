<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2016/8/23
 * Time: 16:33
 */

namespace PhpX\Lang;


abstract class Builder extends Object {
    protected $data;

    public function __construct() {
        $this->data = array();
    }

    public function merge ($array) {
        foreach ($array as $key => $value) {
            $this->data[$key] = $value;
        }
        return $this;
    }

    public function get ($key) {
        return isset($this->data[$key])
            ? $this->data[$key]
            : null;
    }

    public function set ($key, $value) {
        $this->data[$key] = $value;
        return $this;
    }

    /**
     * 获取或写入键值对，即 Getter 或 Setter
     * @param string $key 键
     * @param null|mixed $value 值，若为空，则为读取（getter），否则为写入（setter）
     * @return mixed|null|Builder
     */
    public function valueOf ($key, $value = null) {
        return ($value == null)
            ? $this->get($key)
            : $this->set($key, $value);
    }

    /**
     * @param string $name
     * @param string $arguments
     * @return $this|mixed
     */
    public function __call ($name, $arguments) {
        return ($arguments == null)
            ? $this->get($name)
            : $this->set($name, $arguments[0]);
    }

    /**
     * @return mixed
     */
    public abstract function build ();
}

