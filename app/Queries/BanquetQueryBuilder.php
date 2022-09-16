<?php

namespace App\Queries;

use App\Models\Customer;
use App\Models\Orders\Order;
use App\Models\User;

/**
 * Class BanquetQueryBuilder.
 */
class BanquetQueryBuilder extends BaseQueryBuilder
{
    /**
     * Apply index query conditions.
     *
     * @param User $user
     *
     * @return static
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function index(User $user): static
    {
        if ($user->isStaff()) {
            return $this;
        }

        $this->whereWrapped(
            function (BanquetQueryBuilder $query) use ($user) {
                $query->where('creator_id', $user->id);
                if ($user->customer_id) {
                    $query->orWhere('customer_id', $user->customer_id);
                }
            }
        );

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

    /**
     * @param Order|int ...$orders
     *
     * @return static
     */
    public function withOrder(Order|int ...$orders): static
    {
        $this->join('banquet_order.banquet_id', '=', 'banquets.id')
            ->whereIn('banquet_order.order_id', $this->extract('id', $orders))
            ->select('banquets.*');

        return $this;
    }
}
