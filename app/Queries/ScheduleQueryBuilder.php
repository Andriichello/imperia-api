<?php

namespace App\Queries;

use App\Models\Schedule;
use App\Models\User;

/**
 * Class ScheduleQueryBuilder.
 *
 * @method Schedule|null first($columns = ['*'])
 * @method Schedule|null firstOrFail($columns = ['*'])
 * @method Schedule|null find($columns = ['*'])
 * @method Schedule|null findOrFail($id, $columns = ['*'])
 * @method $this where($column, $operator = null, $value = null, $boolean = 'and')
 * @method $this orWhere($column, $operator = null, $value = null)
 */
class ScheduleQueryBuilder extends BaseQueryBuilder
{
    /**
     * Apply index query conditions.
     *
     * @param User|null $user
     *
     * @return static
     */
    public function index(?User $user = null): static
    {
        $query = parent::index($user);

        if ($user->restaurant_id) {
            $query->withRestaurant($user->restaurant_id);
        }

        return $query;
    }

    /**
     * Only schedules for given weekdays.
     *
     * @param string ...$weekdays
     *
     * @return static
     */
    public function withWeekday(string ...$weekdays): static
    {
        $this->whereIn('workday', $weekdays);

        return $this;
    }

    /**
     * Only schedules for given restaurants.
     *
     * @param mixed ...$restaurants
     *
     * @return static
     */
    public function withRestaurant(mixed ...$restaurants): static
    {
        $ids = $this->extract('id', ...$restaurants);

        if (!empty($ids)) {
            $this->whereIn($this->model->getTable() . '.restaurant_id', $ids);
        }

        return $this;
    }

    /**
     * Only schedules that are default ones.
     *
     * @return static
     */
    public function onlyDefaults(): static
    {
        $this->whereNull('restaurant_id');

        return $this;
    }
}
