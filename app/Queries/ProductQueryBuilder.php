<?php

namespace App\Queries;

use App\Models\Menu;
use App\Models\Restaurant;
use App\Models\User;
use App\Queries\Interfaces\ArchivableInterface;
use App\Queries\Interfaces\CategorizableInterface;
use App\Queries\Interfaces\TaggableInterface;
use App\Queries\Traits\Archivable;
use App\Queries\Traits\Categorizable;
use App\Queries\Traits\Taggable;

/**
 * Class ProductQueryBuilder.
 */
class ProductQueryBuilder extends BaseQueryBuilder implements
    TaggableInterface,
    ArchivableInterface,
    CategorizableInterface
{
    use Taggable;
    use Archivable;
    use Categorizable;

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
     * @param Menu|int ...$menus
     *
     * @return static
     */
    public function withMenu(Menu|int ...$menus): static
    {
        $this->join('menu_product as mp', 'mp.product_id', '=', 'products.id')
            ->whereIn('mp.menu_id', $this->extract('id', ...$menus))
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
        $ids = $this->extract('id', ...$restaurants);

        if (!empty($ids)) {
            $this->whereIn($this->model->getTable() . '.restaurant_id', $ids);
        }

        return $this;
    }
}
