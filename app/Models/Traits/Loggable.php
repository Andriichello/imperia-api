<?php

namespace App\Models\Traits;

use App\Models\BaseModel;
use App\Models\BaseModel;
use App\Models\Morphs\Log;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Trait Loggable.
 *
 * @mixin BaseModel
 */
trait Loggable
{
    /**
     * Logs related to the model.
     *
     * @return MorphMany
     */
    public function logs(): MorphMany
    {
        return $this->morphMany(Log::class, 'loggable', 'loggable_type', 'loggable_id', 'id');
    }
}
