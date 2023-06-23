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
     * @param User $user
     *
     * @return static
     */
    public function index(User $user): static
    {
        $query = parent::index($user);

        if (!empty($user->restaurants)) {
            $query->whereIn('id', $user->restaurants);
        }

        return $query;
    }
}
