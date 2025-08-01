<?php

namespace App\Queries;

use App\Enums\BanquetState;
use App\Models\Customer;
use App\Models\Restaurant;
use App\Models\User;

/**
 * Class BanquetQueryBuilder.
 */
class BanquetQueryBuilder extends BaseQueryBuilder
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
        if ($user->restaurant_id) {
            $this->withRestaurant($user->restaurant_id);
        }

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
     * @param Restaurant|int ...$restaurants
     *
     * @return static
     */
    public function withRestaurant(Restaurant|int ...$restaurants): static
    {
        $ids = $this->extract('id', ...$restaurants);

        if (!empty($ids)) {
            $this->whereIn($this->model->getTable() . '.restaurant_id', $ids);
        }

        return $this;
    }

    /**
     * Only banquets that are in given states
     *
     * @param string ...$states
     *
     * @return static
     */
    public function inState(string ...$states): static
    {
        $this->whereIn('state', $states);

        return $this;
    }

    /**
     * Only banquets that are relevant (new, confirmed, completed).
     *
     * @return static
     */
    public function thatAreRelevant(): static
    {
        $this->inState(BanquetState::New, BanquetState::Confirmed, BanquetState::Completed);

        return $this;
    }

    /**
     *  Only banquets that are irrelevant (postponed, cancelled).
     *
     * @return static
     */
    public function thatAreIrrelevant(): static
    {
        $this->inState(BanquetState::Postponed, BanquetState::Cancelled);

        return $this;
    }
}
