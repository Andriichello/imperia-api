<?php

namespace App\Models\Interfaces;

use App\Models\Morphs\Period;
use DateTime;
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
     * Query builder for periods in time range.
     *
     * @param DateTime|null $start
     * @param DateTime|null $end
     *
     * @return MorphToMany
     */
    public function periodsInRange(?DateTime $start = null, ?DateTime $end = null): MorphToMany;

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

    /**
     * Determines if model has periods that currently affect it.
     *
     * @param DateTime|null $start
     * @param DateTime|null $end
     *
     * @return bool
     */
    public function hasAffectingPeriods(?DateTime $start = null, ?DateTime $end = null): bool;
}
