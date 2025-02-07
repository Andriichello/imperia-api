<?php

namespace App\Enums;

/**
 * Enum OrderKind.
 *
 * @method static OrderKind Banquet()
 * @method static OrderKind Delivery()
 *
 * @SuppressWarnings(PHPMD)
 */
class OrderKind extends Enum
{
    public const Banquet = 'banquet';
    public const Delivery = 'delivery';
}
