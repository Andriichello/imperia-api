<?php

namespace App\Models\Traits;

use App\Models\BaseModel;
use App\Models\Morphs\Category;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Trait Categorizable.
 *
 * @mixin BaseModel
 */
trait Categorizable
{
    /**
     * Categories related to the model.
     *
     * @return MorphMany
     */
    public function categories(): MorphMany
    {
        return $this->morphMany(Category::class, 'categorizable', 'categorizable_type', 'categorizable_id', 'id');
    }
}
