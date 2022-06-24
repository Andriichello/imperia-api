<?php

namespace App\Models\Interfaces;

use App\Models\Morphs\Media;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Collection;

/**
 * Interface MediableInterface.
 *
 * @property Media[]|Collection $media
 */
interface MediableInterface
{
    /**
     * Medias related to the model.
     *
     * @return MorphToMany
     */
    public function media(): MorphToMany;

    /**
     * Attach given media to the model.
     *
     * @param Media ...$media
     *
     * @return void
     */
    public function attachMedia(Media ...$media): void;

    /**
     * Detach given media from the model.
     *
     * @param Media ...$media
     *
     * @return void
     */
    public function detachMedia(Media ...$media): void;

    /**
     * Determines if model has media attached.
     *
     * @return bool
     */
    public function hasMedia(): bool;

    /**
     * Determines if model has all media attached.
     *
     * @param Media ...$media
     *
     * @return bool
     */
    public function hasAllOfMedia(Media ...$media): bool;

    /**
     * Determines if model has any of media attached.
     *
     * @param Media ...$media
     *
     * @return bool
     */
    public function hasAnyOfMedia(Media ...$media): bool;
}
