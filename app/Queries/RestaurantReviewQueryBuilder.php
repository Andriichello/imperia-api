<?php

namespace App\Queries;

use App\Models\RestaurantReview;
use App\Models\User;

/**
 * Class RestaurantReviewQueryBuilder.
 *
 * @method RestaurantReview|null first($columns = ['*'])
 * @method RestaurantReview|null firstOrFail($columns = ['*'])
 * @method RestaurantReview|null find($columns = ['*'])
 * @method RestaurantReview|null findOrFail($id, $columns = ['*'])
 * @method $this where($column, $operator = null, $value = null, $boolean = 'and')
 * @method $this orWhere($column, $operator = null, $value = null)
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
