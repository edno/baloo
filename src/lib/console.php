<?php

namespace Baloo\Lib\Console;

defined('ISCLI') or define('ISCLI', PHP_SAPI === 'cli');

function read_console()
{
    return trim(fgets(STDIN));
}
