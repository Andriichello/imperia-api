<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Class AsyncJob.
 */
abstract class AsyncJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Number of times the job should be tried.
     *
     * @var int
     */
    protected int $attempts = 3;

    /**
     * Set the number of times that the job should be tried.
     *
     * @param int $attempts
     *
     * @return static
     */
    public function setAttempts(int $attempts): static
    {
        $this->attempts = $attempts;

        return $this;
    }
}
