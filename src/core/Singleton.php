<?php

namespace Baloo;

/**
 * @source http://stackoverflow.com/a/7105008/2131802
 */
trait Singleton
{
    protected static $instance;

    final public static function getInstance()
    {
        return isset(static::$instance)
            ? static::$instance
            : static::$instance = new static;
    }

    final private function __construct()
    {
        $this->__init();
    }

    protected function __init()
    {
    }

    final private function __wakeup()
    {
    }

    final private function __clone()
    {
    }

    final private function __sleep()
    {
    }
}
