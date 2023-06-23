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

        if (!$user) {
            return false;
        }

        if ($user->isAdmin()) {
            if (!$user->restaurant_id) {
                return true;
            }

            if ($ability === 'update') {
                return null;
            }
        }

        return false;
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
        if (!$user || !$user->isAdmin()) {
            return false;
        }

        return !$user->restaurant_id ||
            $user->restaurant_id === $restaurant->id;
    }
}
