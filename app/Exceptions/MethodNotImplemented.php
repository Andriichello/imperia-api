<?php

namespace App\Exceptions;

use Exception;

/**
 * Class MethodNotImplemented.
 */
class MethodNotImplemented extends Exception
{
    /**
     * MethodNotImplemented constructor.
     *
     * @param string $class
     * @param string $method
     */
    public function __construct(string $class, string $method)
    {
        parent::__construct("Method '$method' is not implemented in $class");
    }
}
