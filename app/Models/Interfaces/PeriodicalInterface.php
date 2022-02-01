<?php

namespace App\Models\Interfaces;

use App\Models\Morphs\Period;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;

/**
 * Interface PeriodicalInterface.
 *
 * @property Period[]|Collection $periods
 */
interface PeriodicalInterface
{
    /**
     * Logs related to the model.
     *
     * @return MorphMany
     */
    public function periods(): MorphMany;
}
