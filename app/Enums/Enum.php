<?php

namespace App\Enums;

/**
 * Class Enum
 */
class Enum extends \BenSampo\Enum\Enum
{
    /**
     * Get constants map (key => value).
     *
     * @return string[]
     */
    public static function getMap(): array
    {
        return self::getConstants();
    }

    /**
     * Get laravel 'in' validation rule.
     *
     * @return string
     */
    public static function getValidationRule(): string
    {
        return 'in:' . implode(',', static::getValues());
    }
}
