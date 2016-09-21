<?php

namespace PhpX\Lang\Reflection;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionParameter;

final class ReflectionUtils {
    public static function splitMember ($member) {
        return explode("::", $member);
    }
}