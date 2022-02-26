<?php

namespace App\Models\Traits;

use App\Models\BaseModel;
use App\Models\Morphs\Categorizable;
use App\Models\Morphs\Category;
use ClassicO\NovaMediaLibrary\API as MediaLibraryAPI;
use ClassicO\NovaMediaLibrary\Core\Model as MediaModel;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

/**
 * Trait MediableTrait.
 *
 * @mixin BaseModel
 *
 * @property array $media_ids
 * @property array $media
 */
trait MediableTrait
{
    /**
     * Accessor for the image ids property of metadata.
     *
     * @return array
     */
    public function getMediaIdsAttribute(): array
    {
        return Arr::wrap($this->getFromJson('metadata', 'media_ids'));
    }

    /**
     * Mutator for the image ids property of metadata.
     *
     * @param mixed $value
     */
    public function setMediaIdsAttribute(mixed $value): void
    {
        $this->setToJson('metadata', 'media_ids', $value);
    }

    /**
     * Accessor for the image.
     *
     * @return array
     */
    public function getMediaAttribute(): array
    {
        $media = MediaLibraryAPI::getFiles($this->media_ids, null, true);
        return Arr::wrap($media);
    }
}
