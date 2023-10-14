<?php

namespace App\Jobs\Morph;

use App\Jobs\AsyncJob;
use App\Models\Morphs\Alteration;

/**
 * Class PerformAlternations.
 */
class PerformAlternations extends AsyncJob
{
    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        Alteration::query()
            ->thatShouldBePerformed()
            ->each(function (Alteration $alteration) {
                $attributes = $alteration->getJson('metadata');

                $alterable = $alteration->alterable;
                $alterable->fill($attributes);
                $alterable->save();

                $alteration->performed_at = now();
                $alteration->save();
            });
    }
}
