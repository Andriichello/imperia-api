<?php

namespace App\Queries;

use App\Enums\NotificationChannel;
use App\Models\Restaurant;
use App\Models\User;

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
        $this->whereIn('restaurant_id', $this->extract('id', $restaurants));

        return $this;
    }
}
