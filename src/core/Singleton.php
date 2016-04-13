<?php

namespace Baloo;

/**
 * @source http://stackoverflow.com/a/7105008/2131802
 */
trait Singleton 
{
    protected static $__instance;
    
    final public static function getInstance()
    {
        return isset(static::$__instance)
            ? static::$__instance
            : static::$__instance = new static;
    }
    
    final private function __construct() {
        $this->__init();
    }
    
    protected function __init() {}
    
    final private function __wakeup() {}
    
    final private function __clone() {} 
    
}