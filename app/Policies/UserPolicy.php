<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

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
     * Determine whether the user can view any models.
     *
     * @param User $user
     * @return bool
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
     */
    public function create(User $user, string ...$roles): bool
    {
        if (!in_array(UserRole::Admin, $roles)) {
            return $user->isAdmin();
        }

        if (!in_array(UserRole::Manager, $roles)) {
            return $user->isManager();
        }

        return false;
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
        return $user->is($model) || $this->isHigher($user, $model);
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
        return $user->is($model) || $this->isHigher($user, $model);
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
        return $user->is($model) || $this->isHigher($user, $model);
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
        return $user->is($model) || $this->isHigher($user, $model);
    }
}
