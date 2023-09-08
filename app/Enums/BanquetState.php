<?php

namespace App\Enums;

/**
 * Enum BanquetState.
 *
 * @method static BanquetState New()
 * @method static BanquetState Confirmed()
 * @method static BanquetState Postponed()
 * @method static BanquetState Cancelled()
 * @method static BanquetState Completed()
 *
 * @SuppressWarnings(PHPMD)
 */
class BanquetState extends Enum
{
    public const New = 'new';
    public const Confirmed = 'confirmed';
    public const Postponed = 'postponed';
    public const Cancelled = 'cancelled';
    public const Completed = 'completed';
}
