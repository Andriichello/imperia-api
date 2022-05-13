<?php

namespace App\Policies;

use App\Models\Customer;
use App\Models\User;
use App\Policies\Base\CrudPolicy;
use Illuminate\Database\Eloquent\Model;

/**
 * Class CustomerPolicy.
 */
class CustomerPolicy extends CrudPolicy
{
    /**
     * Get the model of the policy.
     *
     * @return Model|string
     */
    public function model(): Model|string
    {
        return Customer::class;
    }

    /**
     * Determine whether the user can view any models.
     *
     * @param User $user
     *
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $user->isStuff();
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
     * @param Customer $customer
     *
     * @return bool
     */
    public function view(User $user, Customer $customer): bool
    {
        return $customer->user_id === $user->id || $user->isStuff();
    }

    /**
     * Determine whether the user can create models.
     *
     * @param User $user
     *
     * @return bool
     */
    public function create(User $user): bool
    {
        return $user->isStuff();
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param Customer $customer
     *
     * @return bool
     */
    public function update(User $user, Customer $customer): bool
    {
        return $customer->user_id === $user->id || $user->isStuff();
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param Customer $customer
     *
     * @return bool
     */
    public function delete(User $user, Customer $customer): bool
    {
        return $customer->user_id === $user->id || $user->isStuff();
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     * @param Customer $customer
     *
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function restore(User $user, Customer $customer): bool
    {
        return $user->isStuff();
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param User $user
     * @param Customer $customer
     *
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function forceDelete(User $user, Customer $customer): bool
    {
        return $user->isStuff();
    }
}
