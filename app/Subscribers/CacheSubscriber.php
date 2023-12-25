<?php

namespace App\Subscribers;

use App\Models\BaseModel;
use App\Models\Cache;
use App\Models\Menu;
use App\Models\Morphs\Alteration;
use App\Models\Morphs\Category;
use App\Models\Product;
use App\Models\Restaurant;
use App\Models\RestaurantReview;
use App\Models\Service;
use App\Models\Space;
use App\Models\Ticket;
use Illuminate\Support\Arr;

/**
 * Class CacheSubscriber.
 */
class CacheSubscriber extends BaseSubscriber
{
    /**
     * Models to their cache settings.
     *
     * @var array|array[]
     */
    protected array $models = [
        Restaurant::class => [
            'groups' => ['restaurants'],
        ],
        RestaurantReview::class => [
            'groups' => ['restaurants'],
        ],
        Menu::class => [
            'groups' => ['menus', 'categories', 'products'],
        ],
        Category::class => [
            'groups' => ['menus', 'categories', 'products', 'services', 'tickets', 'spaces'],
        ],
        Product::class => [
            'groups' => ['menus', 'products', 'categories', 'alterations'],
        ],
        Service::class => [
            'groups' => ['services', 'categories', 'alterations'],
        ],
        Ticket::class => [
            'groups' => ['tickets', 'categories', 'alterations'],
        ],
        Space::class => [
            'groups' => ['spaces', 'categories', 'alterations'],
        ],
        Alteration::class => [
            'groups' => ['alterations', 'menus', 'products', 'services', 'tickets', 'spaces'],
        ],
        // Customer::class => [
        //    'groups' => ['customers'],
        // ],
    ];

    protected function map(): void
    {
        $map = [];

        foreach (array_keys($this->models) as $model) {
            /** @var BaseModel $model */
            $map[$model::eloquentEvent('saved')] = 'clear';
            $map[$model::eloquentEvent('deleted')] = 'clear';
            $map[$model::eloquentEvent('restored')] = 'clear';
        }

        $this->map = $map;
    }

    /**
     * Clear cache for given model's paths and groups.
     *
     * @param BaseModel $model
     *
     * @return void
     */
    public function clear(BaseModel $model): void
    {
        $settings = data_get($this->models, $model::class);

        $paths = data_get($settings, 'paths');
        $groups = data_get($settings, 'groups');

        if (!empty($paths)) {
            $this->clearForPaths(Arr::wrap($paths));
        }

        if (!empty($groups)) {
            $this->clearForGroups(Arr::wrap($groups));
        }
    }

    /**
     * Clear cache for given paths.
     *
     * @param array $paths
     *
     * @return void
     */
    public function clearForPaths(array $paths): void
    {
        if (empty($paths)) {
            return;
        }

        Cache::query()
            ->withAnyOfPaths(...$paths)
            ->delete();
    }

    /**
     * Clear cache for given groups.
     *
     * @param array $groups
     *
     * @return void
     */
    public function clearForGroups(array $groups): void
    {
        if (empty($groups)) {
            return;
        }

        Cache::query()
            ->inAnyOfGroups(...$groups)
            ->delete();
    }
}
