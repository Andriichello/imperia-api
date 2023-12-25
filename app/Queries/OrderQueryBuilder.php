<?php

namespace App\Queries;

use App\Models\Customer;
use App\Models\User;

/**
 * Class OrderQueryBuilder.
 */
class OrderQueryBuilder extends BaseQueryBuilder
{
    /**
     * Apply index query conditions.
     *
     * @param User|null $user
     *
     * @return static
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function index(?User $user = null): static
    {
        if ($user->isStaff()) {
            return $this;
        }

        if ($user->isCustomer()) {
            $this->whereHas(
                'banquet',
                function (BanquetQueryBuilder $query) use ($user) {
                    $query->where('creator_id', $user->id);
                    if ($user->customer_id) {
                        $query->orWhere('customer_id', $user->customer_id);
                    }
                }
            );
        }

        return $this;
    }

    /**
     * Only banquets that were created by given users.
     *
     * @param User|int ...$users
     *
     * @return static
     */
    public function fromCreators(User|int ...$users): static
    {
        $this->whereIn('creator_id', $this->extract('id', ...$users));

        return $this;
    }

    /**
     * Only banquets that were created for given users.
     *
     * @param User|int ...$users
     *
     * @return static
     */
    public function forUsers(User|int ...$users): static
    {
        $this->whereIn('customer_id', $this->extract('customer_id', ...$users));

        return $this;
    }

    /**
     * Only banquets that were created for given customers.
     *
     * @param Customer|int ...$customers
     *
     * @return static
     */
    public function forCustomers(Customer|int ...$customers): static
    {
        $this->whereIn('customer_id', $this->extract('id', ...$customers));

        return $this;
    }
}
