<?php

namespace App\Queries;

use App\Models\DishCategory;
use App\Models\DishMenu;
use App\Models\Restaurant;
use App\Models\User;
use App\Queries\Interfaces\ArchivableInterface;
use App\Queries\Traits\Archivable;

/**
 * Class DishCategoryQueryBuilder.
 *
 * @method DishCategory|null first($columns = ['*'])
 * @method DishCategory|null firstOrFail($columns = ['*'])
 * @method DishCategory|null find($columns = ['*'])
 * @method DishCategory|null findOrFail($id, $columns = ['*'])
 * @method $this where($column, $operator = null, $value = null, $boolean = 'and')
 * @method $this orWhere($column, $operator = null, $value = null)
 */
class DishCategoryQueryBuilder extends BaseQueryBuilder implements
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
            $this->join('dish_menus as dm', 'dm.id', '=', 'dish_categories.menu_id')
                ->whereIn('dm.restaurant_id', $ids)
                ->select('dish_categories.*');
        }

        return $this;
    }
}
