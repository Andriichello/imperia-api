<?php

namespace App\Models\Interfaces;

use App\Models\MenuVersionChange;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;

/**
 * Interface SchedulableInterface.
 *
 * Models, which scheduled versions can change (see `MenuVersionChange::TARGETS`).
 *
 * @property MenuVersionChange[]|Collection $scheduledChanges
 */
interface SchedulableInterface
{
    /**
     * Changes of the model in scheduled versions (applied ones included).
     *
     * @return MorphMany
     */
    public function scheduledChanges(): MorphMany;

    /**
     * Changes of the model in versions, which haven't gone live yet.
     *
     * @return MorphMany
     */
    public function pendingScheduledChanges(): MorphMany;
}
