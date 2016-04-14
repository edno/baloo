<?php

namespace Baloo;

abstract class StaticProxy
{
    public static function __callStatic($name, $arg)
    {
    }
}
