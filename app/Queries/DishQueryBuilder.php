<?php

namespace App\Queries;

use App\Models\DishMenu;
use App\Models\Restaurant;
use App\Models\User;
use App\Queries\Interfaces\ArchivableInterface;
use App\Queries\Traits\Archivable;

/**
 * Class DishQueryBuilder.
 */
class DishQueryBuilder extends BaseQueryBuilder implements
    ArchivableInterface
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

        if ($user && $user->restaurant_id) {
            $query->withRestaurant($user->restaurant_id);
        }

        return $query;
    }

    /**
     * @param DishMenu|int ...$menus
     *
     * @return static
     */
    public function withMenu(DishMenu|int ...$menus): static
    {
        $ids = $this->extract('id', ...$menus);

        if (!empty($ids)) {
            $this->whereIn($this->model->getTable() . '.menu_id', $ids);
        }

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
            $this->join('dish_menus as dm', 'dm.id', '=', 'dishes.menu_id')
                ->whereIn('dm.restaurant_id', $ids)
                ->select('dishes.*');
        }

        return $this;
    }
}
