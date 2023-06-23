<?php

namespace App\Enums;

/**
 * Enum WeightUnit.
 *
 * @method static WeightUnit Gram()
 * @method static WeightUnit Kilogram()
 * @method static WeightUnit Milliliter()
 * @method static WeightUnit Liter()
 * @method static WeightUnit Centimeter()
 * @method static WeightUnit Piece()
 *
 * @SuppressWarnings(PHPMD)
 */
class WeightUnit extends Enum
{
    public const Gram = 'g';
    public const Kilogram = 'kg';
    public const Milliliter = 'ml';
    public const Liter = 'l';
    public const Centimeter = 'cm';
    public const Piece = 'pc';
}
