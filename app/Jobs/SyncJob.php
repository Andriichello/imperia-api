<?php

namespace App\Jobs;

use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Class SyncJob.
 */
abstract class SyncJob
{
    use Dispatchable;
    use SerializesModels;
}
