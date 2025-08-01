<?php

namespace App\Queries;

use App\Models\Product;
use App\Models\Restaurant;
use App\Models\User;
use App\Queries\Traits\Archivable;

/**
 * Class MenuQueryBuilder.
 */
class MenuQueryBuilder extends BaseQueryBuilder
{
    use Archivable;

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
     * @param Product|int ...$products
     *
     * @return static
     */
    public function withProduct(Product|int ...$products): static
    {
        $this->join('menu_product as mp', 'mp.menu_id', '=', 'menus.id')
            ->whereIn('mp.product_id', $this->extract('id', ...$products))
            ->select('menus.*');

        return $this;
    }

    /**
     * @param Restaurant|int ...$restaurants
     *
     * @return static
     */
    public function withRestaurant(Restaurant|int ...$restaurants): static
    {
        $ids = $this->extract('id', ...$restaurants);

        if (!empty($ids)) {
            $this->whereIn($this->model->getTable() . '.restaurant_id', $ids);
        }

        return $this;
    }
}
