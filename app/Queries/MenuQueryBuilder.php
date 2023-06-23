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
        $this->join('restaurant_menu as rm', 'rm.menu_id', '=', 'menus.id')
            ->whereIn('rm.restaurant_id', $this->extract('id', ...$restaurants))
            ->select('menus.*');

        return $this;
    }
}
