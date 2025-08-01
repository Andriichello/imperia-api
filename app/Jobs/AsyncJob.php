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
     * Get the number of seconds to wait before retrying the job.
     *
     * @return int|array
     */
    public function backoff(): int|array
    {
        return $this->backoff ?? 1;
    }
}
