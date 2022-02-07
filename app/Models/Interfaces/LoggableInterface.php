<?php

namespace App\Models\Interfaces;

use App\Models\Morphs\Log;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;

/**
 * Interface LoggableInterface.
 *
 * @property Log[]|Collection $logs
 */
interface LoggableInterface
{
    /**
     * Get fields, changes of which should be logged.
     *
     * @return array
     */
    public function getLogFields(): array;

    /**
     * Determines if log fields have changed since last log.
     *
     * @return bool
     */
    public function logFieldsChanged(): bool;

    /**
     * Logs related to the model.
     *
     * @return MorphMany
     */
    public function logs(): MorphMany;

    /**
     * Attach given logs to the model.
     *
     * @param array ...$metas
     *
     * @return void
     */
    public function attachLogs(array ...$metas): void;

    /**
     * Determines if model has logs attached to it.
     *
     * @return bool
     */
    public function hasLogs(): bool;
}
