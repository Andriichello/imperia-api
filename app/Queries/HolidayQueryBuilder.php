<?php

namespace App\Queries;

use Carbon\CarbonInterface;
use Illuminate\Database\Query\Builder;

/**
 * Class ScheduleQueryBuilder.
 */
class HolidayQueryBuilder extends BaseQueryBuilder
{
    /**
     * Only holidays for given date.
     *
     * @param CarbonInterface $date
     *
     * @return static
     */
    public function relevantOn(CarbonInterface $date): static
    {
        $this->where('date', '=', $date->clone()->setTime(0, 0));

        return $this;
    }

    /**
     * Only holidays relevant from the given date (included).
     *
     * @param CarbonInterface $date
     *
     * @return static
     */
    public function relevantFrom(CarbonInterface $date): static
    {
        $this->where('date', '>=', $date->clone()->setTime(0, 0));

        return $this;
    }

    /**
     * Only holidays relevant until the given date (included).
     *
     * @param CarbonInterface $date
     *
     * @return static
     */
    public function relevantUntil(CarbonInterface $date): static
    {
        $this->where('date', '<=', $date->clone()->setTime(0, 0));

        return $this;
    }

    /**
     * Only holidays that are repeating.
     *
     * @param bool $repeating
     *
     * @return static
     */
    public function whereRepeating(bool $repeating): static
    {
        $this->where('repeating', $repeating);

        return $this;
    }

    /**
     * Only holidays for given restaurants.
     *
     * @param mixed ...$restaurants
     *
     * @return static
     */
    public function withRestaurant(mixed ...$restaurants): static
    {
        $this->whereNested(function (Builder $query) use ($restaurants) {
            $ids = $this->extract('id', ...$restaurants);

            $query->whereIn('restaurant_id', $ids);
            if (in_array(null, $ids)) {
                $query->orWhereNull('restaurant_id');
            }
        });

        return $this;
    }
}
