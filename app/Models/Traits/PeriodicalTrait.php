<?php

namespace App\Models\Traits;

use App\Models\BaseModel;
use App\Models\Morphs\Period;
use App\Models\Morphs\Periodical;
use DateTime;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * Trait PeriodicalTrait.
 *
 * @mixin BaseModel
 */
trait PeriodicalTrait
{
    /**
     * Periods related to the model.
     *
     * @return MorphToMany
     */
    public function periods(): MorphToMany
    {
        return $this->morphToMany(
            Period::class, // related model
            'periodical', // morph relation name
            Periodical::class, // morph relation table
            'periodical_id', // morph table pivot key to current model
            'period_id' // morph table pivot key to related model
        );
    }

    /**
     * Query builder for periods in time range.
     *
     * @param DateTime|null $start
     * @param DateTime|null $end
     *
     * @return MorphToMany
     */
    public function periodsInRange(?DateTime $start = null, ?DateTime $end = null): MorphToMany
    {
        return $this->periods()->where('start_at', '<=', $start ?? now())
            ->where('end_at', '>=', $end ?? now());
    }

    /**
     * Attach given categories to the model.
     *
     * @param Period ...$periods
     *
     * @return void
     */
    public function attachPeriods(Period ...$periods): void
    {
        $this->periods()->attach(extractValues('id', ...$periods));
    }

    /**
     * Detach given periods from the model.
     *
     * @param Period ...$periods
     *
     * @return void
     */
    public function detachPeriods(Period ...$periods): void
    {
        $this->periods()->detach(extractValues('id', ...$periods));
    }

    /**
     * Determines if model has periods attached.
     *
     * @return bool
     */
    public function hasPeriods(): bool
    {
        return $this->periods()->exists();
    }

    /**
     * Determines if model has all periods attached.
     *
     * @param Period ...$periods
     *
     * @return bool
     */
    public function hasAllOfPeriods(Period ...$periods): bool
    {
        $ids = array_map(fn(Period $period) => $period->id, $periods);
        $count = $this->periods()->whereIn('id', $ids)->count();
        return count($periods) === $count;
    }

    /**
     * Determines if model has any of periods attached.
     *
     * @param Period ...$periods
     *
     * @return bool
     */
    public function hasAnyOfPeriods(Period ...$periods): bool
    {
        $ids = array_map(fn(Period $period) => $period->id, $periods);
        return empty($ids) || $this->periods()->whereIn('id', $ids)->exists();
    }

    /**
     * Determines if model has periods that affect it
     * in specified time range.
     *
     * @param DateTime|null $start
     * @param DateTime|null $end
     *
     * @return bool
     */
    public function hasAffectingPeriods(?DateTime $start = null, ?DateTime $end = null): bool
    {
        return $this->periodsInRange($start, $end)->exists();
    }
}
