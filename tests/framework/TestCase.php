<?php

namespace Baloo\UnitTest;

abstract class TestCase extends \PHPUnit_Framework_TestCase
{  
    /**
     * Use reflection for setting private properties
     */
    final public static function setPrivateProperty(string $class, string $property, $value) {
        $reflectedProperty = new \ReflectionProperty($class, $property);
        $reflectedProperty->setAccessible(true);
        $reflectedProperty->setValue($value);
    }
    
    /**
     * Use reflection for accessing private methods
     * @return method result
     */
    final public static function invokePrivateMethod(string $class, string $method, ...$params) {
        $reflectedMethod = new \ReflectionMethod($class, $method);
        $reflectedMethod->setAccessible(true);
        $class = $reflectedMethod->isStatic() ? null : $class;
        return $reflectedMethod->invokeArgs($class, $params);
    }
    
}