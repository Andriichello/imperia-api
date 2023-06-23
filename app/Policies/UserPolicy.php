<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\User;
use App\Policies\Base\CrudPolicy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Response;
use Spatie\Permission\Models\Role;

/**
 * Class UserPolicy.
 */
class UserPolicy extends CrudPolicy
{
    /**
     * Get the model of the policy.
     *
     * @return Model|string
     */
    public function model(): Model|string
    {
        return User::class;
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
        if (!$user) {
            return false;
        }

        if ($user->isPreviewOnly() && !in_array($ability, ['view', 'viewAny', 'update'])) {
            return false;
        }

        return $user->isAdmin();
    }

    /**
     * Determine whether the user can view any models.
     *
     * @param User $user
     *
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
     * @param User $model
     *
     * @return bool
     */
    public function view(User $user, User $model): bool
    {
        return $user->is($model) || $this->isHigher($user, $model);
    }

    /**
     * Determine whether the user can create models.
     *
     * @param User $user
     * @param string ...$roles
     *
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function create(User $user, string ...$roles): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param User $model
     *
     * @return bool
     */
    public function update(User $user, User $model): bool
    {
        if ($user->is($model)) {
            return true;
        }

        if (!$this->isHigher($user, $model)) {
            return false;
        }

        return $this->checkRestaurant($user, $model) && $user->isAdmin();
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param User $model
     *
     * @return bool
     */
    public function delete(User $user, User $model): bool
    {
        if ($user->is($model) && !$user->isPreviewOnly()) {
            return true;
        }

        if (!$this->isHigher($user, $model)) {
            return false;
        }

        return $this->checkRestaurant($user, $model) && $user->isAdmin();
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     * @param User $model
     *
     * @return bool
     */
    public function restore(User $user, User $model): bool
    {
        return $user->is($model)
            || ($this->isHigher($user, $model) && $user->isAdmin());
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param User $user
     * @param User $model
     *
     * @return bool
     */
    public function forceDelete(User $user, User $model): bool
    {
        return $user->is($model)
            || ($this->isHigher($user, $model) && $user->isAdmin());
    }

    /**
     * Determine if user can attach roles to model.
     *
     * @param User $user
     * @param User $model
     *
     * @return bool
     */
    public function attachAnyRole(User $user, User $model): bool
    {
        return $this->isHigher($user, $model) && $user->isAdmin();
    }

    /**
     * Determine if user can attach role to model.
     *
     * @param User $user
     * @param User $model
     * @param Role $role
     *
     * @return bool
     */
    public function attachRole(User $user, User $model, Role $role): bool
    {
        return ($this->isHigher($user, $model) && $user->isAdmin())
            && strtolower(data_get($role, 'name', '')) !== strtolower(UserRole::Admin);
    }

    /**
     * Determine if user can detach role from model.
     *
     * @param User $user
     * @param User $model
     * @param Role $role
     *
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function detachRole(User $user, User $model, Role $role): bool
    {
        return $this->isHigher($user, $model)  && $user->isAdmin();
    }
}
