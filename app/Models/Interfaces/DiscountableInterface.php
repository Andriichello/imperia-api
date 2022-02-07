<?php

namespace App\Models\Interfaces;

use App\Models\Morphs\Discount;
use App\Models\Morphs\Discountable;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

/**
 * Interface DiscountableInterface.
 *
 * @property Discount[]|Collection $discounts
 */
interface DiscountableInterface
{
    /**
     * Discounts related to the model.
     *
     * @return MorphToMany
     */
    public function discounts(): MorphToMany;

    /**
     * Attach given discounts to the model.
     *
     * @param Discount ...$discounts
     *
     * @return void
     */
    public function attachDiscounts(Discount ...$discounts): void;

    /**
     * Detach given discounts from the model.
     *
     * @param Discount ...$discounts
     *
     * @return void
     */
    public function detachDiscounts(Discount ...$discounts): void;

    /**
     * Determines if model has discounts attached.
     *
     * @return bool
     */
    public function hasDiscounts(): bool;

    /**
     * Determines if model has all discounts attached.
     *
     * @param Discount ...$discounts
     *
     * @return bool
     */
    public function hasAllOfDiscounts(Discount ...$discounts): bool;

    /**
     * Determines if model has any of discounts attached.
     *
     * @param Discount ...$discounts
     *
     * @return bool
     */
    public function hasAnyOfDiscounts(Discount ...$discounts): bool;

    /**
     * Get total amount from all attached discounts.
     *
     * @return float
     */
    public function discountsAmount(): float;

    /**
     * Get total percent from all attached discounts.
     *
     * @return float
     */
    public function discountsPercent(): float;

    /**
     * Apply attached discounts to the price.
     *
     * @param float $price
     *
     * @return float
     */
    public function applyDiscounts(float $price): float;
}
