<?php

namespace App\Queries;

use App\Models\Restaurant;
use App\Models\User;

/**
 * Class WaiterQueryBuilder.
 */
class WaiterQueryBuilder extends BaseQueryBuilder
{
    /**
     * Apply index query conditions.
     *
     * @param User|null $user
     *
     * @return static
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function index(User $user = null): static
    {
        $query = parent::index($user);

        if ($user?->restaurant_id) {
            $query->withRestaurant($user->restaurant_id);
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
            $this->whereIn($this->model->getTable() . '.restaurant_id', $ids);
        }

        return $this;
    }
}
