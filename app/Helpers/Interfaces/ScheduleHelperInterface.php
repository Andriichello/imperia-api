<?php

namespace App\Helpers\Interfaces;

use App\Models\Schedule;
use Carbon\CarbonInterface;

/**
 * Interface ScheduleHelperInterface.
 */
interface ScheduleHelperInterface
{
    /**
     * Determine if given date and time is in schedule.
     *
     * @param Schedule $schedule
     * @param CarbonInterface|null $date
     *
     * @return bool
     */
    public function relevantOn(Schedule $schedule, ?CarbonInterface $date = null): bool;

    /**
     * @param Schedule $schedule
     *
     * @return CarbonInterface
     */
    public function next(Schedule $schedule): CarbonInterface;

    /**
     * @param Schedule $schedule
     *
     * @return CarbonInterface
     */
    public function prev(Schedule $schedule): CarbonInterface;
}
