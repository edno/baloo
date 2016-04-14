<?php

namespace Baloo\Packman;

/**
 * class PackmanException
 * Class that manages Packman exceptions.
 */
class PackManException extends \Exception
{
    public function __construct($message = '', $code = 0, $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
