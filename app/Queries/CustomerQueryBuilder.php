<?php

namespace App\Queries;

use App\Models\Customer;
use App\Models\Restaurant;
use App\Models\User;

/**
 * Class CustomerQueryBuilder.
 *
 * @method Customer|null first($columns = ['*'])
 * @method Customer|null firstOrFail($columns = ['*'])
 * @method Customer|null find($columns = ['*'])
 * @method Customer|null findOrFail($id, $columns = ['*'])
 * @method $this where($column, $operator = null, $value = null, $boolean = 'and')
 * @method $this orWhere($column, $operator = null, $value = null)
 */
class CustomerQueryBuilder extends BaseQueryBuilder
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
        $query = parent::index($user);

        if ($user->restaurant_id) {
            $query->withRestaurant($user->restaurant_id);
        }

        if ($user->isStaff()) {
            return $this;
        }

        $this->where('id', $user->customer_id);

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
}
