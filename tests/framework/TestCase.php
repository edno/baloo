<?php

namespace Baloo\UnitTest;

abstract class TestCase extends \PHPUnit_Framework_TestCase
{
    use MockSingleton;
    use ReflectionPrivate;

}
