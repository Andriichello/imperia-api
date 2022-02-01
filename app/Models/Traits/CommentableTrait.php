<?php

namespace App\Models\Traits;

use App\Models\BaseModel;
use App\Models\Morphs\Comment;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;

/**
 * Trait CommentableTrait.
 *
 * @mixin BaseModel
 *
 * @property Comment[]|Collection $comments
 */
trait CommentableTrait
{
    /**
     * Comments related to the model.
     *
     * @return MorphMany
     */
    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    /**
     * Attach given comments to the model.
     *
     * @param string ...$texts
     *
     * @return void
     */
    public function attachComments(string ...$texts): void
    {
        $texts = array_map(fn($text) => compact('text'), $texts);
        $this->comments()->createMany($texts);
    }

    /**
     * Detach given comments from the model.
     *
     * @param string ...$texts
     *
     * @return void
     */
    public function detachComments(string ...$texts): void
    {
        $this->comments()->whereIn('text', $texts)->delete();
    }

    /**
     * Determines if model has comments attached.
     *
     * @return bool
     */
    public function hasComments(): bool
    {
        return $this->comments()->exists();
    }

    /**
     * Determines if model has all comments attached.
     *
     * @param string ...$texts
     *
     * @return bool
     */
    public function hasAllOfComments(string ...$texts): bool
    {
        $texts = array_unique($texts);
        $count = $this->comments()->whereIn('text', $texts)->count();
        return count($texts) <= $count;
    }

    /**
     * Determines if model has any of comments attached.
     *
     * @param string ...$texts
     *
     * @return bool
     */
    public function hasAnyOfComments(string ...$texts): bool
    {
        $texts = array_unique($texts);
        return empty($texts) || $this->comments()->whereIn('text', $texts)->exists();
    }
}
