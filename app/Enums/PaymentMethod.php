<?php

namespace App\Enums;

/**
 * Enum PaymentMethod.
 *
 * @method static PaymentMethod Card()
 * @method static PaymentMethod Cash()
 *
 * @SuppressWarnings(PHPMD)
 */
class PaymentMethod extends Enum
{
    public const Card = 'card';
    public const Cash = 'cash';
}
