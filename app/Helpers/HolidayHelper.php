<?php

namespace App\Helpers;

use App\Helpers\Interfaces\HolidayHelperInterface;
use App\Models\Holiday;
use Carbon\CarbonInterface;

/**
 * Class HolidayHelper.
 */
class HolidayHelper implements HolidayHelperInterface
{
    /**
     * Get the next date of given schedule.
     *
     * @param Holiday $holiday
     * @param CarbonInterface|null $from
     *
     * @return CarbonInterface|null
     */
    public function next(Holiday $holiday, ?CarbonInterface $from = null): ?CarbonInterface
    {
        $next = ($from ?? now())->clone()
            ->addDays();

        return $this->closest($holiday, $next);
    }

    /**
     * Get the closest date of given schedule.
     *
     * @param Holiday $holiday
     * @param CarbonInterface|null $to
     *
     * @return CarbonInterface|null
     */
    public function closest(Holiday $holiday, ?CarbonInterface $to = null): ?CarbonInterface
    {
        $closest = ($to ?? now())->clone()
            ->setDays($holiday->day)
            ->setTime(0, 0);

        if (!$holiday->month && !$holiday->year) {
            if ($holiday->day > $to->day) {
                return $closest;
            }

            return $closest->addMonths();
        }

        if (!$holiday->month) {
            $closest->setYears($holiday->year);

            if ($holiday->year < $to->year) {
                return null;
            }

            if ($holiday->year > $to->year) {
                return $closest->setMonths(1);
            }

            if ($holiday->day > $to->day) {
                return $closest;
            }

            if ($holiday->day < $to->day) {
                return  $to->month === 12 ? null : $closest->addMonths();
            }

            return $closest;
        }

        if (!$holiday->year) {
            $closest->setMonths($holiday->month);

            if ($holiday->month < $to->month || $holiday->day < $to->day) {
                return $closest->addYears();
            }

            return $closest;
        }

        $closest->setMonths($holiday->month)
            ->setYear($holiday->year);

        if ($closest->isSameDay($to)) {
            $closest->setTimeFrom($to);
        }

        return $closest->greaterThanOrEqualTo($to) ? $closest : null;
    }

    /**
     * Get the previous date of given schedule.
     *
     * @param Holiday $holiday
     * @param CarbonInterface|null $from
     *
     * @return CarbonInterface|null
     */
    public function prev(Holiday $holiday, ?CarbonInterface $from = null): ?CarbonInterface
    {
        $from = ($from ?? now())->clone()
            ->subDays();
        $closest = ($from ?? now())->clone()
            ->setDays($holiday->day)
            ->setTime(0, 0);

        if (!$holiday->month && !$holiday->year) {
            if ($holiday->day < $from->day) {
                return $closest;
            }

            return $closest->subMonths();
        }

        if (!$holiday->month) {
            $closest->setYears($holiday->year);

            if ($holiday->year > $from->year) {
                return null;
            }

            if ($holiday->year < $from->year) {
                return $closest->setMonths(12);
            }

            if ($holiday->day < $from->day) {
                return $closest;
            }

            if ($holiday->day > $from->day) {
                return  $from->month === 1 ? null : $closest->subMonths();
            }

            return $closest;
        }

        if (!$holiday->year) {
            $closest->setMonths($holiday->month);

            if ($holiday->month > $from->month || $holiday->day > $from->day) {
                return $closest->subYear();
            }

            return $closest;
        }

        $closest->setMonths($holiday->month)
            ->setYear($holiday->year);

        if ($closest->isSameDay($from)) {
            $closest->setTimeFrom($from);
        }

        return $closest->lessThanOrEqualTo($from) ? $closest : null;
    }
}
