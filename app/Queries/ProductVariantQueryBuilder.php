<?php

namespace App\Queries;

use App\Models\ProductVariant;
use App\Models\Restaurant;
use App\Models\User;

/**
 * Class ProductVariantQueryBuilder.
 *
 * @method ProductVariant|null first($columns = ['*'])
 * @method ProductVariant|null firstOrFail($columns = ['*'])
 * @method ProductVariant|null find($columns = ['*'])
 * @method ProductVariant|null findOrFail($id, $columns = ['*'])
 * @method $this where($column, $operator = null, $value = null, $boolean = 'and')
 * @method $this orWhere($column, $operator = null, $value = null)
 */
class ProductVariantQueryBuilder extends BaseQueryBuilder
{
    /**
     * Apply index query conditions.
     *
     * @param User|null $user
     *
     * @return static
     */
    public function index(?User $user = null): static
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
