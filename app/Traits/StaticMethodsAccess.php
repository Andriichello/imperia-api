<?php

namespace App\Traits;

use App\Exceptions\MethodNotImplemented;

/**
 * Class StaticMethodsAccess.
 */
trait StaticMethodsAccess
{
    /**
     * Get string, which represents the name of the class method.
     *
     * @param string $method
     *
     * @return string
     * @throws MethodNotImplemented
     */
    public static function method(string $method): string
    {
        if (!method_exists(static::class, $method)) {
            throw new MethodNotImplemented(static::class, $method);
        }

        return static::class . '@' . $method;
    }

    /**
     * Get string, which represents eloquent event.
     *
     * @param string $event
     * @param string|null $class
     *
     * @return string
     */
    public static function eloquentEvent(string $event, ?string $class = null): string
    {
        return "eloquent.{$event}: " . ($class ?? static::class);
    }
}
