<?php

namespace App\Queries;

use App\Models\Restaurant;
use App\Models\User;

/**
 * Class ProductVariantQueryBuilder.
 */
class ProductVariantQueryBuilder extends BaseQueryBuilder
{
    /**
     * Apply index query conditions.
     *
     * @param User $user
     *
     * @return static
     */
    public function index(User $user): static
    {
        $query = parent::index($user);

        if (!empty($user->restaurants)) {
            $query->withRestaurant(...$user->restaurants);
        }

        return $query;
    }

    /**
     * @param Restaurant|int ...$restaurants
     *
     * @return static
     */
    public function withRestaurant(Restaurant|int ...$restaurants): static
    {
        $this->join('restaurant_product as rp', 'rp.product_id', '=', 'product_variants.product_id')
            ->whereIn('rp.restaurant_id', $this->extract('id', ...$restaurants))
            ->select('product_variants.*');

        return $this;
    }
}
