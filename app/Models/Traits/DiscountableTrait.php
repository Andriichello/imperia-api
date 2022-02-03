<?php

namespace App\Models\Traits;

use App\Models\BaseModel;
use App\Models\Morphs\Discount;
use App\Models\Morphs\Discountable;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Arr;

/**
 * Trait DiscountableTrait.
 *
 * @mixin BaseModel
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
        $this->discounts()->attach(Arr::pluck($discounts, 'id'));
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
        $this->discounts()->detach(Arr::pluck($discounts, 'id'));
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
}
