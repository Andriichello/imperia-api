<?php

namespace App\Enums;

/**
 * Enum ScheduleDay.
 *
 * @method static Weekday Monday()
 * @method static Weekday Tuesday()
 * @method static Weekday Wednesday()
 * @method static Weekday Thursday()
 * @method static Weekday Friday()
 * @method static Weekday Saturday()
 * @method static Weekday Sunday()
 *
 * @SuppressWarnings(PHPMD)
 */
class Weekday extends Enum
{
    public const Monday = 'monday';
    public const Tuesday = 'tuesday';
    public const Wednesday = 'wednesday';
    public const Thursday = 'thursday';
    public const Friday = 'friday';
    public const Saturday = 'saturday';
    public const Sunday = 'sunday';
}
