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

    /**
     * Get today's weekday.
     *
     * @return string
     */
    public static function today(): string
    {
        return static::fromDayOfWeek(now()->dayOfWeek);
    }

    /**
     * Get next weekday.
     *
     * @param string $weekday
     *
     * @return string
     */
    public static function next(string $weekday): string
    {
        $dayOfWeek = static::toDayOfWeek($weekday) + 1;

        return static::fromDayOfWeek($dayOfWeek % 7);
    }

    /**
     * Get previous weekday.
     *
     * @param string $weekday
     *
     * @return string
     */
    public static function prev(string $weekday): string
    {
        $dayOfWeek = static::toDayOfWeek($weekday) - 1;
        $dayOfWeek = $dayOfWeek < 0 ? 6 : $dayOfWeek;

        return static::fromDayOfWeek($dayOfWeek);
    }

    /**
     * Convert enum value to integer value for day of week.
     *
     * @param string $weekday
     *
     * @return int
     */
    public static function toDayOfWeek(string $weekday): int
    {
        // @phpstan-ignore-next-line
        return match ($weekday) {
            static::Sunday => 0,
            static::Monday => 1,
            static::Tuesday => 2,
            static::Wednesday => 3,
            static::Thursday => 4,
            static::Friday => 5,
            static::Saturday => 6,
        };
    }

    /**
     * Convert day of week integer value to enum value.
     *
     * @param int $dayOfWeek
     *
     * @return string
     */
    public static function fromDayOfWeek(int $dayOfWeek): string
    {
        // @phpstan-ignore-next-line
        return match ($dayOfWeek) {
            0 => static::Sunday,
            1 => static::Monday,
            2 => static::Tuesday,
            3 => static::Wednesday,
            4 => static::Thursday,
            5 => static::Friday,
            6 => static::Saturday,
        };
    }
}
