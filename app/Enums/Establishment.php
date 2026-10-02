<?php

namespace App\Enums;

/**
 * Enum Establishment.
 *
 * Restaurant types, which the website has its own wording for.
 *
 * @method static Establishment Restaurant()
 * @method static Establishment Cafe()
 * @method static Establishment Bakery()
 * @method static Establishment Bistro()
 * @method static Establishment Pizzeria()
 * @method static Establishment Bar()
 *
 * @SuppressWarnings(PHPMD)
 */
class Establishment extends Enum
{
    public const Restaurant = 'restaurant';
    public const Cafe = 'cafe';
    public const Bakery = 'bakery';
    public const Bistro = 'bistro';
    public const Pizzeria = 'pizzeria';
    public const Bar = 'bar';

    /**
     * Establishments with their labels.
     *
     * @return array<string, string>
     */
    public static function getLabels(): array
    {
        return [
            self::Restaurant => 'Restaurant',
            self::Cafe => 'Café',
            self::Bakery => 'Bakery',
            self::Bistro => 'Bistro',
            self::Pizzeria => 'Pizzeria',
            self::Bar => 'Bar',
        ];
    }
}
