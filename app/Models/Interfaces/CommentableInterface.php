<?php

namespace App\Models\Interfaces;

use App\Models\Morphs\Comment;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;

/**
 * Interface CommentableInterface.
 *
 * @property Comment[]|Collection $comments
 */
interface CommentableInterface
{
    /**
     * Comments related to the model.
     *
     * @return MorphMany
     */
    public function comments(): MorphMany;

    /**
     * Attach given comments to the model.
     *
     * @param string ...$texts
     *
     * @return void
     */
    public function attachComments(string ...$texts): void;

    /**
     * Detach given comments from the model.
     *
     * @param string ...$texts
     *
     * @return void
     */
    public function detachComments(string ...$texts): void;

    /**
     * Determines if model has comments attached.
     *
     * @return bool
     */
    public function hasComments(): bool;

    /**
     * Determines if model has all comments attached.
     *
     * @param string ...$texts
     *
     * @return bool
     */
    public function hasAllOfComments(string ...$texts): bool;

    /**
     * Determines if model has any of comments attached.
     *
     * @param string ...$texts
     *
     * @return bool
     */
    public function hasAnyOfComments(string ...$texts): bool;
}
