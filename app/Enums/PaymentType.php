<?php

namespace App\Enums;

/**
 * Enum PaymentType.
 *
 * @method static PaymentType Partner()
 *
 * @SuppressWarnings(PHPMD)
 */
class PaymentType extends Enum
{
    public const Card = 'card';
    public const Cash = 'cash';
}
