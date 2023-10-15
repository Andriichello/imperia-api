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
            ->each(function (Alteration $alteration) use (&$failed) {
                $attributes = $alteration->getJson('metadata');

                try {
                    $alterable = $alteration->alterable;
                    $alterable->fill($attributes);
                    $alterable->save();

                    $alteration->performed_at = now();
                    $alteration->save();
                } catch (Throwable $throwable) {
                    $failed[] = $alteration->id;

                    $alteration->performed_at = null;
                    $alteration->failed_at = now();
                    $alteration->exception = $throwable;
                    $alteration->save();
                }
            });

        if ($failed) {
            throw new Exception(
                'There were errors with alternations: '
                . implode(',', $failed)
            );
        }
    }
}
