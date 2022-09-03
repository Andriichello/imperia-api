<?php

namespace App\Queries;

use Carbon\CarbonInterface;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Arr;

/**
 * Class ScheduleQueryBuilder.
 */
class HolidayQueryBuilder extends BaseQueryBuilder
{
    /**
     * Only holidays for given day.
     *
     * @param int ...$dates
     *
     * @return static
     */
    public function withDay(int ...$dates): static
    {
        $this->whereIn('date', $dates);

        return $this;
    }

    /**
     * Only holidays for given months.
     *
     * @param int|null ...$months
     *
     * @return static
     */
    public function withMonth(?int ...$months): static
    {
        $this->whereIn('month', $months);

        return $this;
    }

    /**
     * Only holidays for given years.
     *
     * @param int|null ...$years
     *
     * @return static
     */
    public function withYear(?int ...$years): static
    {
        $this->whereIn('year', $years);

        return $this;
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
        $closure = function (Builder $query) use ($date) {
            $query->where(
                function (Builder $query) use ($date) {
                    $query->whereNull('day')
                        ->orWhere('day', $date->day);
                }
            );

            $query->where(
                function (Builder $query) use ($date) {
                    $query->whereNull('month')
                        ->orWhere('month', $date->month);
                }
            );

            $query->where(
                function (Builder $query) use ($date) {
                    $query->whereNull('year')
                        ->orWhere('year', $date->year);
                }
            );
        };

        $this->whereNested($closure);

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
        $closure = function (Builder $query) use ($date) {
            $query->whereNested(
                function (Builder $sub) use ($date) {
                    $sub->where(
                        function (Builder $query) use ($date) {
                            $query->whereNull('day')
                                ->orWhere('day', '>=', $date->day);
                        }
                    );

                    $sub->where(
                        function (Builder $query) use ($date) {
                            $query->whereNull('month')
                                ->orWhere('month', '=', $date->month);
                        }
                    );

                    $sub->where(
                        function (Builder $query) use ($date) {
                            $query->whereNull('year')
                                ->orWhere('year', '>=', $date->year);
                        }
                    );
                },
                'or'
            );

            $query->whereNested(
                function (Builder $sub) use ($date) {
                    $sub->where(
                        function (Builder $query) use ($date) {
                            $query->whereNull('month')
                                ->orWhere('month', '>', $date->month);
                        }
                    );

                    $sub->where(
                        function (Builder $query) use ($date) {
                            $query->whereNull('year')
                                ->orWhere('year', '>=', $date->year);
                        }
                    );
                },
                'or'
            );

            $query->whereNested(
                function (Builder $sub) use ($date) {
                    $sub->where(
                        function (Builder $query) use ($date) {
                            $query->whereNull('year')
                                ->orWhere('year', '>', $date->year);
                        }
                    );
                },
                'or'
            );
        };

        $this->whereNested($closure);

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
        $closure = function (Builder $query) use ($date) {
            $query->whereNested(
                function (Builder $sub) use ($date) {
                    $sub->where(
                        function (Builder $query) use ($date) {
                            $query->whereNull('day')
                                ->orWhere('day', '<=', $date->day);
                        }
                    );

                    $sub->where(
                        function (Builder $query) use ($date) {
                            $query->whereNull('month')
                                ->orWhere('month', '=', $date->month);
                        }
                    );

                    $sub->where(
                        function (Builder $query) use ($date) {
                            $query->whereNull('year')
                                ->orWhere('year', '<=', $date->year);
                        }
                    );
                },
                'or'
            );

            $query->whereNested(
                function (Builder $sub) use ($date) {
                    $sub->where(
                        function (Builder $query) use ($date) {
                            $query->whereNull('month')
                                ->orWhere('month', '<', $date->month);
                        }
                    );

                    $sub->where(
                        function (Builder $query) use ($date) {
                            $query->whereNull('year')
                                ->orWhere('year', '<=', $date->year);
                        }
                    );
                },
                'or'
            );

            $query->whereNested(
                function (Builder $sub) use ($date) {
                    $sub->where(
                        function (Builder $query) use ($date) {
                            $query->whereNull('year')
                                ->orWhere('year', '<', $date->year);
                        }
                    );
                },
                'or'
            );
        };

        $this->whereNested($closure);

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

    /**
     * Only holidays that are default ones.
     *
     * @return static
     */
    public function onlyDefaults(): static
    {
        $this->whereNull('restaurant_id');

        return $this;
    }
}
