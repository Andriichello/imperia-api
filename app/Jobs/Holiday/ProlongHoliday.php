<?php

namespace App\Jobs\Holiday;

use App\Jobs\AsyncJob;
use App\Models\Holiday;
use Exception;

/**
 * Class ProlongHoliday.
 */
class ProlongHoliday extends AsyncJob
{
    /**
     * @var Holiday
     */
    protected Holiday $holiday;

    /**
     * RepeatHoliday constructor.
     *
     * @param Holiday $holiday
     */
    public function __construct(Holiday $holiday)
    {
        $this->holiday = $holiday;
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws Exception
     */
    public function handle(): void
    {
        $shouldBeProlonged = $this->holiday->date->isPast()
            && !$this->holiday->date->isSameDay(now());

        if (!$shouldBeProlonged) {
            return;
        }

        while ($this->holiday->date->isPast()) {
            $this->holiday->date->addYear();
        }

        $this->holiday->save();
    }
}
