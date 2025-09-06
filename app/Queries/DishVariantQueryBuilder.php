<?php

namespace App\Queries;

use App\Models\Dish;
use App\Models\DishMenu;
use App\Models\Restaurant;
use App\Models\User;

/**
 * Class DishVariantQueryBuilder.
 */
class DishVariantQueryBuilder extends BaseQueryBuilder
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

        if ($user && $user->restaurant_id) {
            $query->withRestaurant($user->restaurant_id);
        }

        return $query;
    }

    /**
     * @param Dish|int ...$dishes
     *
     * @return static
     */
    public function withDish(Dish|int ...$dishes): static
    {
        $ids = $this->extract('id', ...$dishes);

        if (!empty($ids)) {
            $this->whereIn($this->model->getTable() . '.dish_id', $ids);
        }

        return $this;
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
            $this->join('dishes as d', 'd.id', '=', 'dish_variants.dish_id')
                ->whereIn('d.menu_id', $ids)
                ->select('dish_variants.*');
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
            $this->join('dishes as d', 'd.id', '=', 'dish_variants.dish_id')
                ->join('dish_menus as dm', 'dm.id', '=', 'd.menu_id')
                ->whereIn('dm.restaurant_id', $ids)
                ->select('dish_variants.*');
        }

        return $this;
    }
}
