<?php

namespace App\Models\Traits;

use App\Models\BaseModel;
use App\Models\MenuVersionChange;
use App\Queries\MenuVersionQueryBuilder;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;

/**
 * Trait SchedulableTrait.
 *
 * @mixin BaseModel
 *
 * @property MenuVersionChange[]|Collection $scheduledChanges
 * @property MenuVersionChange[]|Collection $pendingScheduledChanges
 */
trait SchedulableTrait
{
    /**
     * Changes of the model in scheduled versions (applied ones included).
     *
     * @return MorphMany
     */
    public function scheduledChanges(): MorphMany
    {
        return $this->morphMany(MenuVersionChange::class, 'target');
    }

    /**
     * Changes of the model in versions, which haven't gone live yet.
     *
     * @return MorphMany
     */
    public function pendingScheduledChanges(): MorphMany
    {
        /** @var MorphMany $morphMany */
        $morphMany = $this->scheduledChanges();
        // @phpstan-ignore-next-line
        $morphMany->whereHas('version', fn (MenuVersionQueryBuilder $query) => $query->pending());

        return $morphMany;
    }
}
