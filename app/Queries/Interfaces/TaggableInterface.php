<?php

namespace App\Queries\Interfaces;

use App\Models\Morphs\Tag;

/**
 * Interface TaggableInterface.
 */
interface TaggableInterface
{
    /**
     * @param Tag ...$tags
     *
     * @return static
     */
    public function withAllOfTags(Tag ...$tags): static;

    /**
     * @param Tag ...$tags
     *
     * @return static
     */
    public function withAnyOfTags(Tag ...$tags): static;
}
