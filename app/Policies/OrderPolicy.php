<?php

namespace App\Policies;

use App\Models\Orders\Order;
use App\Models\User;
use App\Policies\Base\CrudPolicy;
use Illuminate\Database\Eloquent\Model;

/**
 * Class OrderPolicy.
 */
class OrderPolicy extends CrudPolicy
{
    /**
     * Get the model of the policy.
     *
     * @return Model|string
     */
    public function model(): Model|string
    {
        return Order::class;
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
     * @param Order $order
     *
     * @return bool
     */
    public function view(User $user, Order $order): bool
    {
        return $user->isStaff()
            || $order->banquet->isCreator($user)
            || $order->banquet->isCustomer($user);
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
     * @param Order $order
     *
     * @return bool
     */
    public function update(User $user, Order $order): bool
    {
        if ($order->banquet) {
            return $order->banquet->canBeEditedBy($user);
        }

        return $order->canBeEditedBy($user);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param Order $order
     *
     * @return bool
     */
    public function delete(User $user, Order $order): bool
    {
        return $order->canBeEditedBy($user);
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     * @param Order $order
     *
     * @return bool
     */
    public function restore(User $user, Order $order): bool
    {
        return $order->canBeEditedBy($user);
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param User $user
     * @param Order $order
     *
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function forceDelete(User $user, Order $order): bool
    {
        return $user->isStaff();
    }
}
