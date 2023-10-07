<?php

namespace App\Models\Interfaces;

use App\Models\Morphs\Tag;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Collection;

/**
 * Interface TaggableInterface.
 *
 * @property Tag[]|Collection $tags
 */
interface TaggableInterface
{
    /**
     * Tags related to the model.
     *
     * @return MorphToMany
     */
    public function tags(): MorphToMany;

    /**
     * Attach given tags to the model.
     *
     * @param Tag ...$tags
     *
     * @return void
     */
    public function attachTags(Tag ...$tags): void;

    /**
     * Detach given tags from the model.
     *
     * @param Tag ...$tags
     *
     * @return void
     */
    public function detachTags(Tag ...$tags): void;

    /**
     * Determines if model has tags attached.
     *
     * @return bool
     */
    public function hasTags(): bool;

    /**
     * Determines if model has all tags attached.
     *
     * @param Tag ...$tags
     *
     * @return bool
     */
    public function hasAllOfTags(Tag ...$tags): bool;

    /**
     * Determines if model has any of tags attached.
     *
     * @param Tag ...$tags
     *
     * @return bool
     */
    public function hasAnyOfTags(Tag ...$tags): bool;
}
