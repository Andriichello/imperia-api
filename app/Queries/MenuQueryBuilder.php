<?php

namespace App\Queries;

use App\Models\Product;
use App\Models\Restaurant;
use App\Queries\Traits\Archivable;

/**
 * Class MenuQueryBuilder.
 */
class MenuQueryBuilder extends BaseQueryBuilder
{
    use Archivable;

    /**
     * @param Product|int ...$products
     *
     * @return static
     */
    public function withProduct(Product|int ...$products): static
    {
        $this->join('menu_product.menu_id', '=', 'menus.id')
            ->whereIn('menu_product.product_id', $this->extract('id', $products))
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
        $this->join('restaurant_menu.menu_id', '=', 'menus.id')
            ->whereIn('restaurant_menu.restaurant_id', $this->extract('id', $restaurants))
            ->select('menus.*');

        return $this;
    }
}
