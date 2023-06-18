<?php

namespace App\Policies;

use App\Models\RestaurantReview;
use App\Models\User;
use App\Policies\Base\CrudPolicy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;

/**
 * Class RestaurantReviewPolicy.
 */
class RestaurantReviewPolicy extends CrudPolicy
{
    /**
     * Get the model of the policy.
     *
     * @return Model|string
     */
    public function model(): Model|string
    {
        return RestaurantReview::class;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param User|null $user
     *
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function create(?User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User|null $user
     * @param RestaurantReview $review
     *
     * @return bool
     */
    public function update(?User $user, RestaurantReview $review): bool
    {
        if ($user->isAdmin() || $user->isStaff()) {
            return true;
        }

        return $review->ip === Request::ip();
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User|null $user
     * @param RestaurantReview $review
     *
     * @return bool
     */
    public function delete(?User $user, RestaurantReview $review): bool
    {
        if ($user->isAdmin() || $user->isStaff()) {
            return true;
        }

        return $review->ip === Request::ip();
    }
}
