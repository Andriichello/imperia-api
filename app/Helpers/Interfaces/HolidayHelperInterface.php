<?php

namespace App\Helpers\Interfaces;

use App\Models\Holiday;
use Carbon\CarbonInterface;

/**
 * Interface HolidayHelperInterface.
 */
interface HolidayHelperInterface
{
    /**
     * @param Holiday $holiday
     *
     * @return CarbonInterface|null
     */
    public function next(Holiday $holiday): ?CarbonInterface;

    /**
     * Get the closest date of given schedule.
     *
     * @param Holiday $holiday
     * @param CarbonInterface|null $to
     *
     * @return CarbonInterface|null
     */
    public function closest(Holiday $holiday, ?CarbonInterface $to = null): ?CarbonInterface;

    /**
     * @param Holiday $holiday
     *
     * @return CarbonInterface|null
     */
    public function prev(Holiday $holiday): ?CarbonInterface;
}
