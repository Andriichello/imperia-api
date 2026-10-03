<?php

namespace App\Jobs\Version;

use App\Jobs\AsyncJob;
use App\Repositories\Editor\VersionApplier;

/**
 * Class ApplyDueVersions.
 *
 * Applies the scheduled versions, whose time has come. A version, which can't be applied,
 * is marked as failed with the reason (nothing of it is applied), the others still are.
 */
class ApplyDueVersions extends AsyncJob
{
    /**
     * Execute the job.
     *
     * @param VersionApplier $applier
     *
     * @return void
     */
    public function handle(VersionApplier $applier): void
    {
        $applier->applyDue();
    }
}
