<?php

namespace App\Policies;

use App\Models\Banquet;
use App\Models\User;
use App\Policies\Base\CrudPolicy;
use Illuminate\Database\Eloquent\Model;

/**
 * Class BanquetPolicy.
 */
class BanquetPolicy extends CrudPolicy
{
    /**
     * Get the model of the policy.
     *
     * @return Model|string
     */
    public function model(): Model|string
    {
        return Banquet::class;
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
        return true;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
     * @param Banquet $banquet
     *
     * @return bool
     */
    public function view(User $user, Banquet $banquet): bool
    {
        return $user->isStaff()
            || $banquet->isCreator($user)
            || $banquet->isCustomer($user);
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
        return true;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param Banquet $banquet
     *
     * @return bool
     */
    public function update(User $user, Banquet $banquet): bool
    {
        return $banquet->canBeEditedBy($user);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param Banquet $banquet
     *
     * @return bool
     */
    public function delete(User $user, Banquet $banquet): bool
    {
        return $banquet->canBeEditedBy($user);
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     * @param Banquet $banquet
     *
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function restore(User $user, Banquet $banquet): bool
    {
        return $banquet->canBeEditedBy($user);
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param User $user
     * @param Banquet $banquet
     *
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function forceDelete(User $user, Banquet $banquet): bool
    {
        return $banquet->canBeEditedBy($user) && $user->isStaff();
    }
}
