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
     * @param Media|int ...$media
     *
     * @return static
     */
    public function attachMedia(Media|int ...$media): static;

    /**
     * Detach given media from the model.
     *
     * @param Media|int ...$media
     *
     * @return static
     */
    public function detachMedia(Media|int ...$media): static;

    /**
     * Order media in the same order as given.
     *
     * @param Media|int ...$media
     *
     * @return static
     */
    public function orderMedia(Media|int ...$media): static;

    /**
     * Set model's media.
     *
     * @param Media|int ...$media
     *
     * @return static
     */
    public function setMedia(Media|int ...$media): static;

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
