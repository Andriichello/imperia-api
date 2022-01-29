<?php

namespace App\Models\Traits;

use App\Models\BaseModel;
use App\Models\Morphs\Log;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

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
        DB::transaction(function () use ($metas) {
            foreach ($metas as $meta) {
                $log = new Log([
                    'loggable_id' => $this->id,
                    'loggable_type' => $this->type,
                ]);
                $log->setJson('metadata', $meta);
                $log->save();
            }
        });
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
}
