<?php

namespace App\Enums;

/**
 * Class Enum
 */
class Enum extends \BenSampo\Enum\Enum
{
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
