<?php

namespace App\Queries;

use App\Models\User;

/**
 * Class ScheduleQueryBuilder.
 */
class ScheduleQueryBuilder extends BaseQueryBuilder
{
    /**
     * Apply index query conditions.
     *
     * @param User $user
     *
     * @return static
     */
    public function index(User $user): static
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
            $this->whereIn('restaurant_id', $ids);
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
