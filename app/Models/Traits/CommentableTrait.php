<?php

namespace App\Models\Traits;

use App\Models\BaseModel;
use App\Models\BaseModel;
use App\Models\Morphs\Comment;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Trait Commentable.
 *
 * @mixin BaseModel
 */
trait Commentable
{
    /**
     * Get all comments for model as a target.
     *
     * @return MorphMany
     */
    public function targetComments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'target', 'target_type', 'target_id', 'id');
    }

    /**
     * Get all comments for model as a container.
     *
     * @return MorphMany
     */
    public function containerComments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'container', 'container_type', 'container_id', 'id');
    }
}
