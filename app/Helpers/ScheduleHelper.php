<?php

namespace App\Helpers;

use App\Helpers\Interfaces\ScheduleHelperInterface;
use App\Models\Schedule;
use Carbon\CarbonInterface;

/**
 * Class ScheduleHelper.
 */
class ScheduleHelper implements ScheduleHelperInterface
{
    /**
     * Determine if given date and time is in schedule.
     *
     * @param Schedule $schedule
     * @param CarbonInterface|null $date
     *
     * @return bool
     */
    public function relevantOn(Schedule $schedule, ?CarbonInterface $date = null): bool
    {
        $date = ($date ?? now())->clone();

        if ($schedule->restaurant && $schedule->restaurant->timezone_offset) {
            $date->addHours($schedule->restaurant->timezone_offset);
        }

        $beg = $date->clone()
            ->setHours($schedule->beg_hour)
            ->setMinutes($schedule->beg_minute);

        $end = $date->clone()
            ->setHours($schedule->end_hour)
            ->setMinutes($schedule->end_minute);

        if ($schedule->beg_hour > $schedule->end_hour) {
            if ($date->is($schedule->weekday)) {
                $end->addDay();
            }

            if ($date->clone()->subDay()->is($schedule->weekday)) {
                $beg->subDay();
            }
        }

        return $date->between($beg, $end)
            && ($beg->is($schedule->weekday) || $end->is($schedule->weekday));
    }

    /**
     * Get the next date of given schedule.
     *
     * @param Schedule $schedule
     * @param CarbonInterface|null $from
     *
     * @return CarbonInterface
     */
    public function next(Schedule $schedule, ?CarbonInterface $from = null): CarbonInterface
    {
        $date = ($from ?? now())->clone();

        if ($schedule->restaurant && $schedule->restaurant->timezone_offset) {
            $date->addHours($schedule->restaurant->timezone_offset);
        }

        if ($date->is($schedule->weekday)) {
            $beg = $date->clone()
                ->setTime($schedule->beg_hour, $schedule->end_hour);

            if ($beg->isAfter($date)) {
                return $beg;
            }
        }

        return $date->next($schedule->weekday)
            ->setTime($schedule->beg_hour, $schedule->beg_minute);
    }

    /**
     * Get the closest date of given schedule.
     *
     * @param Schedule $schedule
     * @param CarbonInterface|null $to
     *
     * @return CarbonInterface
     */
    public function closest(Schedule $schedule, ?CarbonInterface $to = null): CarbonInterface
    {
        $date = ($to ?? now())->clone();

        if ($schedule->restaurant && $schedule->restaurant->timezone_offset) {
            $date->addHours($schedule->restaurant->timezone_offset);
        }

        return $this->relevantOn($schedule, $date)
            ? $date : $this->next($schedule, $date);
    }

    /**
     * Get the previous date of given schedule.
     *
     * @param Schedule $schedule
     * @param CarbonInterface|null $from
     *
     * @return CarbonInterface
     */
    public function prev(Schedule $schedule, ?CarbonInterface $from = null): CarbonInterface
    {
        $date = ($from ?? now())->clone();

        if ($schedule->restaurant && $schedule->restaurant->timezone_offset) {
            $date->addHours($schedule->restaurant->timezone_offset);
        }

        return $date
            ->previous($schedule->weekday)
            ->setTime($schedule->beg_hour, $schedule->beg_minute);
    }
}
