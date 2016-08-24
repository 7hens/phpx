<?php

namespace PhpX\Lang;
use Exception;
use ReflectionClass;

trait ObjectTrait {

    public function mergePropertyArray ($array) {
        foreach ($array as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

    public function valueOf ($key, $value = null) {
        if (property_exists($this, $key)) {
            if ($value == null) {
                return $this->$key;
            }
            $this->$key = $value;
            return $this;
        }
        return null;
    }

    /**
     * 获取一个对象，默认直接调用构造函数
     * @return object
     */
    public static function getInstance () {
        $arguments = func_get_args();
        $class = new ReflectionClass(get_called_class());
        return $class->newInstanceArgs($arguments);
    }

    private static function getPropertyGetter ($property) {
        return 'get' . ucfirst($property);
    }

    private static function getPropertySetter ($property) {
        return 'set' . ucfirst($property);
    }

    /**
     * setter
     * @param string $name key
     * @param mixed $value value
     * @throws Exception
     */
    public function __set($name, $value) {
        $getter = self::getPropertyGetter($name);
        $setter = self::getPropertySetter($name);
        if (method_exists($this, $setter)) {
            $this->$setter($value);
        } elseif (method_exists($this, $getter)) {
            throw new Exception('Setting read-only property: ' . get_class($this) . '::' . $name);
        } else {
            throw new Exception('Setting unknown property: ' . get_class($this) . '::' . $name);
        }
    }

    /**
     * getter
     * @param string $name key
     * @return mixed value
     * @throws Exception
     */
    public function __get($name) {
        $getter = self::getPropertyGetter($name);
        $setter = self::getPropertySetter($name);
        if (method_exists($this, $getter)) {
            return $this->$getter();
        } elseif (method_exists($this, $setter)) {
            throw new Exception('Getting write-only property: ' . get_class($this) . '::' . $name);
        } else {
            throw new Exception('Getting unknown property: ' . get_class($this) . '::' . $name);
        }
    }

    public function __isset($name) {
        $getter = self::getPropertyGetter($name);
        $setter = self::getPropertySetter($name);
        if (method_exists($this, $getter)) {
            return $this->$getter() !== null;
        } else {
            return false;
        }
    }

    public function __unset($name) {
        $getter = self::getPropertyGetter($name);
        $setter = self::getPropertySetter($name);
        if (method_exists($this, $setter)) {
            $this->$setter(null);
        } elseif (method_exists($this, $getter)) {
            throw new Exception('Unsetting read-only property: ' . get_class($this) . '::' . $name);
        }
    }

    public function __call($name, $arguments) {
        throw new Exception('Calling unknown method: ' . get_class($this) . "::$name()");
    }
}
