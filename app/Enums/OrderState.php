<?php

namespace App\Enums;

/**
 * Enum OrderState.
 *
 * @method static OrderState New()
 * @method static OrderState Confirmed()
 * @method static OrderState Postponed()
 * @method static OrderState Cancelled()
 * @method static OrderState Completed()
 *
 * @SuppressWarnings(PHPMD)
 */
class OrderState extends Enum
{
    public const New = 'new';
    public const Confirmed = 'confirmed';
    public const Postponed = 'postponed';
    public const Cancelled = 'cancelled';
    public const Completed = 'completed';
}
