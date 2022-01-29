<?php

namespace App\Models\Traits;

use App\Models\BaseModel;
use App\Models\Morphs\Comment;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Trait CommentableTrait.
 *
 * @mixin BaseModel
 *
 * @property Comment[]|Collection $targetComments
 * @property Comment[]|Collection $containerComments
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
        DB::transaction(function () use ($texts) {
            foreach ($texts as $text) {
                Comment::query()
                    ->create([
                        'text' => $text,
                        'commentable_id' => $this->id,
                        'commentable_type' => $this->type,
                    ]);
            }
        });
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
        DB::transaction(function () use ($texts) {
            Comment::query()
                ->where('commentable_id', $this->id)
                ->where('commentable_type', $this->type)
                ->whereIn('text', $texts)
                ->delete();
        });
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
