<?php

namespace App\Models\Interfaces;

use App\Models\Morphs\Period;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Collection;

/**
 * Interface PeriodicalInterface.
 *
 * @property Period[]|Collection $periods
 */
interface PeriodicalInterface
{
    /**
     * Periods related to the model.
     *
     * @return MorphToMany
     */
    public function periods(): MorphToMany;

    /**
     * Attach given periods to the model.
     *
     * @param Period ...$periods
     *
     * @return void
     */
    public function attachPeriods(Period ...$periods): void;

    /**
     * Detach given periods from the model.
     *
     * @param Period ...$periods
     *
     * @return void
     */
    public function detachPeriods(Period ...$periods): void;

    /**
     * Determines if model has periods attached.
     *
     * @return bool
     */
    public function hasPeriods(): bool;

    /**
     * Determines if model has all periods attached.
     *
     * @param Period ...$periods
     *
     * @return bool
     */
    public function hasAllOfPeriods(Period ...$periods): bool;

    /**
     * Determines if model has any of periods attached.
     *
     * @param Period ...$periods
     *
     * @return bool
     */
    public function hasAnyOfPeriods(Period ...$periods): bool;
}
