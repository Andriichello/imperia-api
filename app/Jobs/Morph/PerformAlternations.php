<?php

namespace App\Jobs\Morph;

use App\Jobs\AsyncJob;
use App\Models\Morphs\Alteration;
use Carbon\Carbon;
use Exception;
use RuntimeException;
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
                $attributes = $alteration->getJson('metadata');

                try {
                    $alterable = $alteration->alterable;

                    if (!$alterable) {
                        throw new RuntimeException(
                            "Model {$alteration->alterable_type} #{$alteration->alterable_id} doesn't exist."
                        );
                    }

                    $alterable->fill($attributes);
                    $alterable->save();

                    $alteration->performed_at = Carbon::now();
                    $alteration->save();
                } catch (Throwable $throwable) {
                    $failed[] = $alteration->id;

                    $alteration->performed_at = null;
                    $alteration->failed_at = Carbon::now();
                    $alteration->exception = $throwable;
                    $alteration->save();
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
