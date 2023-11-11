<?php

namespace App\Queries;

use App\Models\User;
use Carbon\CarbonInterface;

/**
 * Class HolidayQueryBuilder.
 */
class HolidayQueryBuilder extends BaseQueryBuilder
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
    public function repeating(bool $repeating): static
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
        $ids = $this->extract('id', ...$restaurants);

        if (!empty($ids)) {
            $this->whereIn($this->model->getTable() . '.restaurant_id', $ids);
        }

        return $this;
    }
}
