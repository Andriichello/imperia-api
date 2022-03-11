<?php

namespace App\Models\Traits;

use App\Models\BaseModel;
use App\Models\Morphs\Media;
use App\Queries\MediaQueryBuilder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

/**
 * Trait MediableTrait.
 *
 * @mixin BaseModel
 *
 * @property array $media_ids
 * @property Collection $media
 */
trait MediableTrait
{
    /**
     * Create query for related media.
     *
     * @return MediaQueryBuilder
     */
    public function media(): MediaQueryBuilder
    {
        return Media::query()->whereIn('id', $this->media_ids);
    }

    /**
     * @return Collection
     */
    public function getMediaAttribute(): Collection
    {
        return $this->media()->get();
    }

    /**
     * @return array
     */
    public function getMediaIdsAttribute(): array
    {
        return Arr::wrap($this->getFromJson('metadata', 'media_ids'));
    }

    /**
     * @param mixed $value
     */
    public function setMediaIdsAttribute(mixed $value): void
    {
        $this->setToJson('metadata', 'media_ids', $value);
    }
}
