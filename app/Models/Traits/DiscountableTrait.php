<?php

namespace App\Models\Traits;

use App\Models\BaseModel;
use App\Models\Morphs\Discount;
use App\Models\Morphs\Discountable;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

/**
 * Trait DiscountableTrait.
 *
 * @mixin BaseModel
 *
 * @property float $discounted_total
 * @property float $discounts_amount
 * @property float $discounts_percent
 *
 * @property Discount[]|Collection $discounts
 */
trait DiscountableTrait
{
    /**
     * Discounts related to the model.
     *
     * @return MorphToMany
     */
    public function discounts(): MorphToMany
    {
        return $this->morphToMany(
            Discount::class, // related model
            'discountable', // morph relation name
            Discountable::class, // morph relation table
            'discountable_id', // morph table pivot key to current model
            'discount_id' // morph table pivot key to related model
        );
    }

    /**
     * Attach given discounts to the model.
     *
     * @param Discount ...$discounts
     *
     * @return void
     */
    public function attachDiscounts(Discount ...$discounts): void
    {
        $this->discounts()->attach(extractValues('id', ...$discounts));
    }

    /**
     * Detach given discounts from the model.
     *
     * @param Discount ...$discounts
     *
     * @return void
     */
    public function detachDiscounts(Discount ...$discounts): void
    {
        $this->discounts()->detach(extractValues('id', ...$discounts));
    }

    /**
     * Determines if model has discounts attached.
     *
     * @return bool
     */
    public function hasDiscounts(): bool
    {
        return $this->discounts()->exists();
    }

    /**
     * Determines if model has all discounts attached.
     *
     * @param Discount ...$discounts
     *
     * @return bool
     */
    public function hasAllOfDiscounts(Discount ...$discounts): bool
    {
        $ids = array_map(fn(Discount $discount) => $discount->id, $discounts);
        $count = $this->discounts()->whereIn('id', $ids)->count();
        return count($discounts) === $count;
    }

    /**
     * Determines if model has any of discounts attached.
     *
     * @param Discount ...$discounts
     *
     * @return bool
     */
    public function hasAnyOfDiscounts(Discount ...$discounts): bool
    {
        $ids = array_map(fn(Discount $discount) => $discount->id, $discounts);
        return empty($ids) || $this->discounts()->whereIn('id', $ids)->exists();
    }

    /**
     * Total amount from all attached discounts.
     *
     * @return float
     */
    public function getDiscountsAmountAttribute(): float
    {
        return $this->discounts()->sum('amount');
    }

    /**
     * Total percent from all attached discounts.
     *
     * @return float
     */
    public function getDiscountsPercentAttribute(): float
    {
        return min(100, $this->discounts()->sum('percent'));
    }

    /**
     * Total after applying all attached discounts.
     *
     * @return float
     */
    public function getDiscountedTotalAttribute(): float
    {
        return $this->applyDiscounts(data_get($this, 'total', 0.0));
    }


    /**
     * Apply attached discounts to the price.
     *
     * @param float $price
     *
     * @return float
     */
    public function applyDiscounts(float $price): float
    {
        if (empty($price)) {
            return 0.0;
        }
        $discount = $this->getDiscountsAmountAttribute() + $price * ($this->getDiscountsPercentAttribute() / 100.0);
        return $discount < $price ? $price - $discount : 0.0;
    }
}
