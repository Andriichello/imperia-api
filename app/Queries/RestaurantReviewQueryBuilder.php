<?php

namespace App\Queries;

use App\Models\User;

/**
 * Class RestaurantReviewQueryBuilder.
 */
class RestaurantReviewQueryBuilder extends BaseQueryBuilder
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

        if ($user->restaurant_id) {
            $query->where('restaurant_id', $user->restaurant_id);
        }

        return $query;
    }
}
