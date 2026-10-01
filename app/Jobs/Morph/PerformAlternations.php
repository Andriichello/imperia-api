<?php

namespace App\Jobs\Morph;

use App\Jobs\AsyncJob;
use App\Models\Morphs\Alteration;
use Exception;
use Throwable;

/**
 * Class PerformAlternations.
 */
class PerformAlternations extends AsyncJob
{
    /**
     * Execute the job.
     *
     * @return void
     * @throws Exception
     */
    public function handle(): void
    {
        $failed = [];

        Alteration::query()
            ->thatShouldBePerformed()
            ->lazyById()
            // @phpstan-ignore-next-line
            ->each(function (Alteration $alteration) use (&$failed) {
                try {
                    $alteration->perform();
                } catch (Throwable $throwable) {
                    $failed[] = $alteration->id;

                    $alteration->markAsFailed($throwable);
                }
            });

        if ($failed) {
            throw new Exception(
                'There were errors with alterations: '
                . implode(',', $failed)
            );
        }
    }
}
