<?php

namespace Baloo\Lib\Console;

const ISCLI = (PHP_SAPI === 'cli');

function readConsole() {
    return trim(fgets(STDIN));
}