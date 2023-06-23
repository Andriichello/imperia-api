<?php

namespace App\Policies;

use App\Models\Restaurant;
use App\Models\User;
use App\Policies\Base\CrudPolicy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Response;

/**
 * Class RestaurantPolicy.
 */
class RestaurantPolicy extends CrudPolicy
{
    /**
     * Get the model of the policy.
     *
     * @return Model|string
     */
    public function model(): Model|string
    {
        return Restaurant::class;
    }

    /**
     * Perform pre-authorization checks.
     *
     * @param User|null $user
     * @param string $ability
     *
     * @return Response|bool|null
     */
    public function before(?User $user, string $ability): Response|bool|null
    {
        if (in_array($ability, ['viewAny', 'view'])) {
            return true;
        }

        if (!$user || in_array($ability, ['create', 'delete', 'forceDelete', 'restore'])) {
            return false;
        }

        return $user->isAdmin();
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User|null $user
     * @param Restaurant $restaurant
     *
     * @return bool
     */
    public function update(?User $user, Restaurant $restaurant): bool
    {
        if (!$user) {
            return false;
        }

        return empty($user->restaurants) ||
            in_array($restaurant->id, $user->restaurants);
    }
}
