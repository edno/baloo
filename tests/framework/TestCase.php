<?php

namespace Baloo\UnitTests\Framework;

abstract class TestCase extends \PHPUnit_Framework_TestCase
{
    use MockSingleton;
    use ReflectionPrivate;
}
