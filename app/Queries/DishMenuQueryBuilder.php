<?php

namespace App\Queries;

use App\Models\Restaurant;
use App\Models\User;
use App\Queries\Interfaces\ArchivableInterface;
use App\Queries\Traits\Archivable;

/**
 * Class DishMenuQueryBuilder.
 */
class DishMenuQueryBuilder extends BaseQueryBuilder implements
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
