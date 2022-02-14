<?php

namespace App\Enums;

/**
 * Enum BanquetState.
 *
 * @method static BanquetState Draft()
 * @method static BanquetState New()
 * @method static BanquetState Processing()
 * @method static BanquetState Cancelled()
 * @method static BanquetState Completed()
 *
 * @SuppressWarnings(PHPMD)
 */
class BanquetState extends Enum
{
    public const Draft = 'draft';
    public const New = 'new';
    public const Cancelled = 'cancelled';
    public const Completed = 'completed';
    public const Processing = 'processing';
}
