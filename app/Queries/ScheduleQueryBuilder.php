<?php

namespace App\Queries;

/**
 * Class ScheduleQueryBuilder.
 */
class ScheduleQueryBuilder extends BaseQueryBuilder
{
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
