<?php

namespace App\Repositories\Traits;

use App\Models\Interfaces\DiscountableInterface;
use App\Models\Morphs\Discount;
use Illuminate\Support\Arr;

/**
 * Trait DiscountableRepositoryTrait.
 */
trait DiscountableRepositoryTrait
{
    /**
     * Create discounts from attributes for model, which is discountable.
     *
     * @param DiscountableInterface $discountable
     * @param array $attributes
     *
     * @return bool
     */
    public function createDiscounts(DiscountableInterface $discountable, array $attributes): bool
    {
        if (Arr::has($attributes, 'discounts')) {
            $ids = $this->extractIdsFromDiscounts($attributes['discounts']);
            $ids = array_unique($ids);

            $discounts = Discount::query()
                ->whereIn('id', $ids)
                ->get();

            $discountable->attachDiscounts(...$discounts->all());
        }

        return true;
    }

    /**
     * Update discounts from attributes for model, which is discountable.
     *
     * @param DiscountableInterface $discountable
     * @param array $attributes
     *
     * @return bool
     */
    public function updateDiscounts(DiscountableInterface $discountable, array $attributes): bool
    {
        if (Arr::has($attributes, 'discounts')) {
            $ids = $this->extractIdsFromDiscounts($attributes['discounts']);
            $ids = array_unique($ids);

            $discountable->discounts()
                ->whereNotIn('id', $ids)
                ->delete();

            if (!empty($ids)) {
                $attachedIds = $discountable->discounts()->pluck('id')->all();
                $discounts = Discount::query()
                    ->whereIn('id', array_diff($ids, $attachedIds))
                    ->get();

                $discountable->attachDiscounts(...$discounts->all());
            }
        }

        return true;
    }

    /**
     * Extract texts from discounts.
     *
     * @param array $discounts
     *
     * @return array
     */
    private function extractIdsFromDiscounts(array $discounts): array
    {
        return Arr::pluck($discounts, 'discount_id');
    }
}
