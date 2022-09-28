<?php

namespace App\Jobs\Holiday;

use App\Jobs\AsyncJob;
use App\Models\Holiday;
use Carbon\Carbon;
use Exception;

/**
 * Class DispatchProlongHolidays.
 */
class DispatchProlongHolidays extends AsyncJob
{
    /**
     * Execute the job.
     *
     * @return void
     * @throws Exception
     */
    public function handle(): void
    {
        Holiday::query()
            ->repeating(true)
            ->relevantUntil(Carbon::yesterday())
            ->each(function (Holiday $holiday) {
                dispatch(new ProlongHoliday($holiday));
            });
    }
}
