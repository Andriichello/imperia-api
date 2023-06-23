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

        if ($user->restaurant_id) {
            $query->withRestaurant($user->restaurant_id);
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
        $ids = $this->extract('id', ...$restaurants);

        $this->join('products as p', 'p.id', '=', 'product_variants.product_id')
            ->whereIn('p.restaurant_id', $ids)
            ->select('product_variants.*');

        return $this;
    }
}
