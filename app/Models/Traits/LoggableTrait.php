<?php

namespace App\Models\Traits;

use App\Jobs\Morph\LogIfModelChanged;
use App\Models\BaseModel;
use App\Models\Morphs\Log;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

/**
 * Trait LoggableTrait.
 *
 * @mixin BaseModel
 *
 * @property Log[]|Collection $logs
 */
trait LoggableTrait
{
    /**
     * Get fields, changes of which should be logged.
     *
     * @return array
     */
    public function logFields(): array
    {
        return $this->logFields ?? [];
    }

    /**
     * Determines if log fields have changed since last log.
     *
     * @return bool
     */
    public function logFieldsChanged(): bool
    {
        $fields = $this->logFields();
        if (empty($fields)) {
            return false;
        }

        $last = $this->lastLogFieldsValues();
        if ($last === null || !Arr::has($last, $fields)) {
            return true;
        }

        $current = $this->logFieldsValues();

        return json_encode($last) !== json_encode($current);
    }

    /**
     * Get current model's log fields values.
     *
     * @return array
     */
    public function logFieldsValues(): array
    {
        $values = [];

        foreach ($this->logFields() as $field) {
            $values[$field] = $this->$field;
        }

        return $values;
    }

    /**
     * Get current model's last log fields values.
     *
     * @return array|null
     */
    public function lastLogFieldsValues(): array|null
    {
        /** @var Log|null $log */
        $log = $this->logs()->latest()->first();

        return $log?->getJson('metadata');
    }

    /**
     * Logs related to the model.
     *
     * @return MorphMany
     */
    public function logs(): MorphMany
    {
        return $this->morphMany(Log::class, 'loggable');
    }

    /**
     * Attach given logs to the model.
     *
     * @param array ...$metas
     *
     * @return void
     */
    public function attachLogs(array ...$metas): void
    {
        $metas = array_map(fn($meta) => ['metadata' => json_encode($meta)], $metas);
        $this->logs()->createMany($metas);
    }

    /**
     * Determines if model has logs attached to it.
     *
     * @return bool
     */
    public function hasLogs(): bool
    {
        return $this->logs()->exists();
    }

    /**
     * Boot loggable trait.
     *
     * @return void
     */
    public static function bootLoggableTrait(): void
    {
        static::created(function (BaseModel $model) {
            dispatch(new LogIfModelChanged($model, 'created'));
        });

        static::updated(function (BaseModel $model) {
            dispatch(new LogIfModelChanged($model, 'updated'));
        });
    }
}
