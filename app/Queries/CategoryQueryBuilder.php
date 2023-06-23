<?php

namespace App\Queries;

use App\Models\Restaurant;
use App\Models\User;

/**
 * Class CategoryQueryBuilder.
 */
class CategoryQueryBuilder extends BaseQueryBuilder
{
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
        $this->join('restaurant_category as rc', 'rc.category_id', '=', 'categories.id')
            ->whereIn('rc.restaurant_id', $this->extract('id', ...$restaurants))
            ->select('categories.*');

        return $this;
    }
}
