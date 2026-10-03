<?php

namespace App\Models\Traits;

use App\Helpers\MediaHelper;
use App\Models\Menu;
use App\Models\Morphs\Category;
use App\Models\Morphs\Media;
use App\Models\Morphs\Mediable;
use App\Models\Product;
use App\Queries\MediaQueryBuilder;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

/**
 * Trait MediableTrait.
 *
 * @property Media[]|Collection $media
 * @property Media[]|Collection $allMedia
 * @property Media[]|Collection $default_media
 */
trait MediableTrait
{
    /**
     * Class name to default media relative path mappings.
     *
     * @var array
     */
    protected static array $defaultMediaMap = [
        Menu::class => '/media/defaults/menu.svg',
        Category::class => '/media/defaults/category.svg',
        Product::class => '/media/defaults/dish.svg',
    ];

    /**
     * Default media collection.
     *
     * @var Collection
     */
    protected static Collection $defaultMedia;

    /**
     * Media related to the model, which guests see (hidden ones are left out).
     *
     * @return MorphToMany
     */
    public function media(): MorphToMany
    {
        return $this->allMedia()
            ->wherePivot('is_hidden', false);
    }

    /**
     * Media related to the model, hidden ones included (for the admin).
     *
     * @return MorphToMany
     */
    public function allMedia(): MorphToMany
    {
        return $this->morphToMany(
            Media::class, // related model
            'mediable', // morph relation name
            Mediable::class, // morph relation table
            'mediable_id', // morph table pivot key to current model
            'media_id' // morph table pivot key to related model
        )->withPivot('order', 'is_hidden')
            ->orderByPivot('order');
    }

    /**
     * Query for related default media.
     *
     * @return MediaQueryBuilder
     */
    public static function defaultMedia(): MediaQueryBuilder
    {
        $helper = new MediaHelper();
        $path = data_get(static::$defaultMediaMap, static::class);

        return Media::query()
            ->folder($helper->folder($path))
            ->disk('public')
            ->name($helper->name($path));
    }

    /**
     * Accessor for the related default media.
     *
     * @return Collection
     */
    public static function getDefaultMediaAttribute(): Collection
    {
        if (isset(static::$defaultMedia)) {
            return static::$defaultMedia;
        }

        return static::$defaultMedia = static::defaultMedia()->get();
    }

    /**
     * Attach given media to the model.
     *
     * @param Media|int ...$media
     *
     * @return static
     */
    public function attachMedia(Media|int ...$media): static
    {
        $this->allMedia()->attach(extractValues('id', ...$media));

        return $this;
    }

    /**
     * Detach given media from the model.
     *
     * @param Media|int ...$media
     *
     * @return static
     */
    public function detachMedia(Media|int ...$media): static
    {
        $this->allMedia()->detach(extractValues('id', ...$media));

        return $this;
    }

    /**
     * Order media in the same order as given.
     *
     * @param Media|int ...$media
     *
     * @return static
     */
    public function orderMedia(Media|int ...$media): static
    {
        $ids = extractValues('id', ...$media);

        foreach ($ids as $index => $id) {
            $this->allMedia()
                ->updateExistingPivot($id, ['order' => $index], false);
        }

        return $this;
    }

    /**
     * Set model's media.
     *
     * @param Media|int ...$media
     *
     * @return static
     */
    public function setMedia(Media|int ...$media): static
    {
        $given = extractValues('id', ...$media);
        $attached = $this->allMedia()->pluck('id')->all();

        $add = array_diff($given, $attached);
        $remove = array_diff($attached, $given);

        $this->attachMedia(...$add)
            ->detachMedia(...$remove)
            ->orderMedia(...$given);

        return $this;
    }

    /**
     * Set model's media in the given order, each one shown to guests or hidden:
     * `[['id' => 4, 'is_hidden' => false], ...]`.
     *
     * @param array $media
     *
     * @return static
     */
    public function setMediaWithVisibility(array $media): static
    {
        $this->setMedia(...array_map(fn (array $item) => (int) $item['id'], $media));

        foreach ($media as $item) {
            $this->allMedia()
                ->updateExistingPivot($item['id'], ['is_hidden' => (bool) ($item['is_hidden'] ?? false)], false);
        }

        return $this;
    }

    /**
     * Determines if model has media attached.
     *
     * @return bool
     */
    public function hasMedia(): bool
    {
        return $this->media()->exists();
    }

    /**
     * Determines if model has all media attached.
     *
     * @param Media ...$media
     *
     * @return bool
     */
    public function hasAllOfMedia(Media ...$media): bool
    {
        $ids = array_map(fn(Media $item) => $item->id, $media);
        $count = $this->media()->whereIn('id', $ids)->count();
        return count($media) === $count;
    }

    /**
     * Determines if model has any of media attached.
     *
     * @param Media ...$media
     *
     * @return bool
     */
    public function hasAnyOfMedia(Media ...$media): bool
    {
        $ids = array_map(fn(Media $item) => $item->id, $media);
        return empty($ids) || $this->media()->whereIn('id', $ids)->exists();
    }
}
