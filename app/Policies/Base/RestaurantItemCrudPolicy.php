<?php

namespace App\Policies\Base;

use App\Models\Menu;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Response;

/**
 * Class RestaurantItemCrudPolicy.
 */
abstract class RestaurantItemCrudPolicy extends CrudPolicy
{
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
        if (in_array($ability, ['view', 'viewAny'])) {
            return true;
        }

        if (!$user->isAdmin()) {
            return false;
        }

        return null;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param User $user
     *
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param Menu $model
     *
     * @return bool
     */
    public function update(User $user, Model $model): bool
    {
        return $user->isAdmin() &&
            $this->restaurantCheck($user, $model);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param Menu $model
     *
     * @return bool
     */
    public function delete(User $user, Model $model): bool
    {
        return $user->isAdmin() &&
            $this->restaurantCheck($user, $model);
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     * @param Menu $model
     *
     * @return bool
     */
    public function restore(User $user, Model $model): bool
    {
        return $user->isAdmin() &&
            $this->restaurantCheck($user, $model);
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param User $user
     * @param Menu $model
     *
     * @return bool
     */
    public function forceDelete(User $user, Model $model): bool
    {
        return $user->isAdmin() &&
            $this->restaurantCheck($user, $model);
    }
}
