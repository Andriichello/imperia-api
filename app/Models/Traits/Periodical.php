<?php

namespace App\Models\Traits;

use App\Models\BaseModel;
use App\Models\BaseModel;
use App\Models\Morphs\Period;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Trait Periodical.
 *
 * @mixin BaseModel
 */
trait Periodical
{
    /**
     * Logs related to the model.
     *
     * @return MorphMany
     */
    public function periods(): MorphMany
    {
        return $this->morphMany(Period::class, 'periodical', 'periodical_type', 'periodical_id', 'id');
    }
}
