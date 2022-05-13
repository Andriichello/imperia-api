<?php

namespace App\Queries;

use App\Models\Customer;
use App\Models\User;

/**
 * Class BanquetQueryBuilder.
 */
class BanquetQueryBuilder extends BaseQueryBuilder
{
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
