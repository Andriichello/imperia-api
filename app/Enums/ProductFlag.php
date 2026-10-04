<?php

namespace App\Enums;

/**
 * Enum ProductFlag.
 *
 * @method static ProductFlag Hit()
 * @method static ProductFlag Vegan()
 * @method static ProductFlag Vegetarian()
 * @method static ProductFlag LactoseFree()
 * @method static ProductFlag DairyFree()
 * @method static ProductFlag PlantMilk()
 * @method static ProductFlag LowCalorie()
 * @method static ProductFlag HighCalorie()
 * @method static ProductFlag HighProtein()
 * @method static ProductFlag LowFat()
 * @method static ProductFlag HighFat()
 * @method static ProductFlag Hotness()
 * @method static ProductFlag LowHotness()
 * @method static ProductFlag MediumHotness()
 * @method static ProductFlag HighHotness()
 * @method static ProductFlag ExtremeHotness()
 * @method static ProductFlag WithSoy()
 * @method static ProductFlag WithCelery()
 * @method static ProductFlag WithWheat()
 * @method static ProductFlag WithSesame()
 * @method static ProductFlag WithNuts()
 * @method static ProductFlag WithPeanuts()
 * @method static ProductFlag WithFish()
 * @method static ProductFlag WithShellfish()
 * @method static ProductFlag WithEggs()
 * @method static ProductFlag WithSeeds()
 * @method static ProductFlag WithMilk()
 *
 * @SuppressWarnings(PHPMD)
 */
class ProductFlag extends Enum
{
    /** Popular */
    public const Hit = 'hit';

    /** Vegan - Vegetarian */
    public const Vegan = 'vegan';
    public const Vegetarian = 'vegetarian';

    /** Lactose */
    public const LactoseFree = 'lactose-free';
    public const DairyFree = 'dairy-free';
    public const PlantMilk = 'plant-milk';

    /** Calorie */
    public const LowCalorie = 'low-calorie';
    public const HighCalorie = 'high-calorie';

    /** Protein */
    public const HighProtein = 'high-protein';

    /** Fat */
    public const LowFat = 'low-fat';
    public const HighFat = 'high-fat';

    /** Hotness */
    public const Hotness = 'hotness';
    public const LowHotness = 'low-hotness';
    public const MediumHotness = 'medium-hotness';
    public const HighHotness = 'high-hotness';
    public const ExtremeHotness = 'extreme-hotness';

    /** Allergens */
    public const WithSoy = 'alg-soy';
    public const WithCelery = 'alg-celery';
    public const WithWheat = 'alg-wheat';
    public const WithSesame = 'alg-sesame';
    public const WithNuts = 'alg-nuts';
    public const WithPeanuts = 'alg-peanuts';
    public const WithFish = 'alg-fish';
    public const WithShellfish = 'alg-shellfish';
    public const WithEggs = 'alg-eggs';
    public const WithSeeds = 'alg-seeds';
    public const WithMilk = 'alg-milk';

    /**
     * Diet and nutrition tags with their labels.
     *
     * @return array<string, string>
     */
    public static function getTagLabels(): array
    {
        return [
            self::Hit => 'Hit',
            self::Vegan => 'Vegan',
            self::Vegetarian => 'Vegetarian',
            self::LactoseFree => 'Lactose-free',
            self::DairyFree => 'Dairy-free',
            self::PlantMilk => 'Plant milk',
            self::LowCalorie => 'Low-calorie',
            self::HighCalorie => 'High-calorie',
            self::HighProtein => 'High-protein',
            self::LowFat => 'Low-fat',
            self::HighFat => 'High-fat',
        ];
    }

    /**
     * Hotness levels with their labels (the same words as on the website).
     *
     * @return array<string, string>
     */
    public static function getHotnessLabels(): array
    {
        return [
            self::Hotness => 'Spicy (level not set)',
            self::LowHotness => 'Mild',
            self::MediumHotness => 'Medium',
            self::HighHotness => 'Spicy',
            self::ExtremeHotness => 'Extra spicy',
        ];
    }

    /**
     * Allergens with their labels.
     *
     * @return array<string, string>
     */
    public static function getAllergenLabels(): array
    {
        return [
            self::WithMilk => 'Milk',
            self::WithEggs => 'Eggs',
            self::WithWheat => 'Wheat',
            self::WithNuts => 'Nuts',
            self::WithPeanuts => 'Peanuts',
            self::WithSoy => 'Soy',
            self::WithFish => 'Fish',
            self::WithShellfish => 'Shellfish',
            self::WithCelery => 'Celery',
            self::WithSesame => 'Sesame',
            self::WithSeeds => 'Seeds',
        ];
    }

    /**
     * All flags with their labels.
     *
     * @return array<string, string>
     */
    public static function getLabels(): array
    {
        return array_merge(
            static::getTagLabels(),
            static::getHotnessLabels(),
            static::getAllergenLabels(),
        );
    }
}
