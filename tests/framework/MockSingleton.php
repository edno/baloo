<?php

namespace Baloo\UnitTest;

/**
 * @source: http://tech.zumba.com/2012/11/26/singleton-class-phpunit-mocking/
 */
trait MockSingleton
{
    public function getMockFromSingleton($type, $methods = array(), array $arguments = array(),
                                         $mockClassName = '', $callAutoload = true, $cloneArguments = true,
                                         $callOriginalMethods = false, $proxyTarget = null) {
        $mock = call_user_func('self::getMock', $type, $methods, $arguments, $mockClassName,
                               false, false, $callAutoload, $cloneArguments, $callOriginalMethods, $proxyTarget);
        $ref = new \ReflectionProperty($type, '__instance');
        $ref->setAccessible(true);
        $ref->setValue(null, $mock);
        return $mock;
    }

}
