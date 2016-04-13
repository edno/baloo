<?php

namespace Baloo\UnitTest;

trait ReflectionPrivate
{
    /**
     * Use reflection for setting private properties
     */
    final public static function setPrivateProperty($class, string $property, $value) {
        if(is_object($class)) {
            $classname = get_class($class);
        } else {
            $classname = $class;
        }
        $reflectedProperty = new \ReflectionProperty($classname, $property);
        $reflectedProperty->setAccessible(true);
        if(is_object($class)) { 
            $reflectedProperty->setValue($class, $value);
        } else {
            $reflectedProperty->setValue($value);
        }
    }
    
    /**
     * Use reflection for accessing private methods
     * @return method result
     */
    final public static function invokePrivateMethod($class, string $method, ...$params) {
        $reflectedMethod = new \ReflectionMethod($class, $method);
        $reflectedMethod->setAccessible(true);
        $class = $reflectedMethod->isStatic() ? null : $class;
        return $reflectedMethod->invokeArgs($class, $params);
    }
	
}