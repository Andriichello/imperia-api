<?php

namespace App\Queries;

use App\Models\Menu;
use App\Models\Restaurant;
use App\Queries\Interfaces\ArchivableInterface;
use App\Queries\Interfaces\CategorizableInterface;
use App\Queries\Traits\Archivable;
use App\Queries\Traits\Categorizable;

/**
 * Class ProductQueryBuilder.
 */
class ProductQueryBuilder extends BaseQueryBuilder implements
    ArchivableInterface,
    CategorizableInterface
{
    use Archivable;
    use Categorizable;

    /**
     * @param Menu|int ...$menus
     *
     * @return static
     */
    public function withMenu(Menu|int ...$menus): static
    {
        $this->join('menu_product.product_id', '=', 'products.id')
            ->whereIn('menu_product.menu_id', $this->extract('id', $menus))
            ->select('products.*');

        return $this;
    }

    /**
     * @param Restaurant|int ...$restaurants
     *
     * @return static
     */
    public function withRestaurant(Restaurant|int ...$restaurants): static
    {
        $this->join('restaurant_product.product_id', '=', 'products.id')
            ->whereIn('restaurant_product.restaurant_id', $this->extract('id', $restaurants))
            ->select('products.*');

        return $this;
    }
}
