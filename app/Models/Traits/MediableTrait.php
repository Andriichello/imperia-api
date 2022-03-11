<?php

namespace App\Models\Traits;

use App\Models\BaseModel;
use App\Models\Menu;
use App\Models\Morphs\Category;
use App\Models\Morphs\Media;
use App\Models\Product;
use App\Models\Service;
use App\Models\Space;
use App\Models\Ticket;
use App\Queries\MediaQueryBuilder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Trait MediableTrait.
 *
 * @mixin BaseModel
 *
 * @property array $media_ids
 * @property Collection $media
 * @property Collection $default_media
 */
trait MediableTrait
{
    /**
     * Class name to default media relative path mappings.
     *
     * @var array
     */
    protected static array $defaultMediaMap = [
        Menu::class => 'defaults/menu.svg',
        Category::class => 'defaults/category.svg',
        Space::class => 'defaults/table.svg',
        Ticket::class => 'defaults/ticket.svg',
        Product::class => 'defaults/dish.svg',
        Service::class => 'defaults/action.svg',
    ];

    /**
     * Default media collection.
     *
     * @var Collection
     */
    protected static Collection $defaultMedia;

    /**
     * Query for related default media.
     *
     * @return MediaQueryBuilder
     */
    public static function defaultMedia(): MediaQueryBuilder
    {
        $parts = Str::of(data_get(static::$defaultMediaMap, static::class))
            ->explode('/')
            ->filter();

        return Media::query()->where('name', $parts->pop())
            ->fromFolder($parts->implode('/'));
    }

    /**
     * @return Collection
     */
    public static function getDefaultMediaAttribute(): Collection
    {
        return static::$defaultMedia = static::$defaultMedia ?? static::defaultMedia()->get();
    }

    /**
     * Query for related media.
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
}
