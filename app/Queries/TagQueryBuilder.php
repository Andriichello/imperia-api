<?php

namespace App\Queries;

use App\Models\Restaurant;
use App\Models\User;
use App\Queries\Traits\Archivable;

/**
 * Class CategoryQueryBuilder.
 */
class TagQueryBuilder extends BaseQueryBuilder
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
     * Limit categories to only those that have given target.
     *
     * @param ?string $target
     *
     * @return static
     */
    public function target(?string $target): static
    {
        $this->where('target', $target);

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
