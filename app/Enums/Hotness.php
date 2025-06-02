<?php

namespace App\Enums;

/**
 * Enum Hotness.
 *
 * @method static Hotness Unknown()
 * @method static Hotness Low()
 * @method static Hotness Medium()
 * @method static Hotness High()
 * @method static Hotness Ultra()
 *
 * @SuppressWarnings(PHPMD)
 */
class Hotness extends Enum
{
    public const Unknown = 'Unknown';
    public const Low = 'Low';
    public const Medium = 'Medium';
    public const High = 'High';
    public const Ultra = 'Ultra';
}
