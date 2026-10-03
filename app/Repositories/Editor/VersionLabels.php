<?php

namespace App\Repositories\Editor;

use App\Helpers\ContentLocale;
use App\Models\BaseModel;
use App\Models\Dish;
use App\Models\DishCategory;
use App\Models\DishMenu;
use App\Models\DishVariant;
use App\Models\Interfaces\TranslatableInterface;
use App\Models\MenuVersionChange;
use App\Models\Restaurant;
use App\Models\RestaurantNote;
use Illuminate\Support\Collection;

/**
 * Class VersionLabels.
 *
 * Labels of the items scheduled changes are about, in their restaurant's default language:
 * `['name' => 'Chicken broth', 'size' => ['weight' => '350', 'weight_unit' => 'g'],
 * 'path' => ['Main menu', 'Soups']]`. A new item is labelled by its planned values.
 */
class VersionLabels
{
    /**
     * Relations an item's label needs, by its type.
     *
     * @var array<string, string[]>
     */
    protected const RELATIONS = [
        'dish-variants' => ['dish.menu', 'dish.category'],
        'dishes' => ['menu', 'category'],
        'dish-categories' => ['menu'],
    ];

    /**
     * Set labels of the changes.
     *
     * @param Collection<int, MenuVersionChange> $changes
     *
     * @return void
     */
    public function label(Collection $changes): void
    {
        $items = $this->load($changes, fn (MenuVersionChange $change) => $change->isNew() ? null : [
            $change->target_type,
            $change->target_id,
        ]);
        $parents = $this->load($changes, fn (MenuVersionChange $change) => $change->isNew() ? [
            MenuVersionChange::TARGETS[$change->target_type]['parent'],
            $change->parent_id,
        ] : null);

        foreach ($changes as $change) {
            $label = $change->isNew()
                ? $this->ofNewItem($change, $parents[$change->id] ?? null)
                : $this->ofItem($items[$change->id] ?? null);

            $change->label = $label;
        }
    }

    /**
     * Items (or parents) of the changes, by the changes' ids.
     *
     * @param Collection<int, MenuVersionChange> $changes
     * @param callable $key returns the type and id of the change's model, or null
     *
     * @return array<int, BaseModel>
     */
    protected function load(Collection $changes, callable $key): array
    {
        $keys = $changes->mapWithKeys(fn (MenuVersionChange $change) => [$change->id => $key($change)])
            ->filter();
        $models = [];

        foreach ($keys->groupBy(fn (array $key) => $key[0]) as $type => $ofType) {
            /** @var class-string<BaseModel> $class */
            $class = MenuVersionChange::TARGETS[$type]['class'];

            $models[$type] = $class::query()
                ->withoutGlobalScopes()
                ->with(static::RELATIONS[$type] ?? [])
                ->findMany($ofType->pluck(1)->unique()->all())
                ->keyBy('id');
        }

        /** @var array<int, BaseModel> $items */
        $items = $keys->map(fn (array $key) => $models[$key[0]]->get($key[1]))->filter()->all();

        return $items;
    }

    /**
     * Label of an existing item (none, when it's gone).
     *
     * @param BaseModel|null $item
     *
     * @return array|null
     */
    protected function ofItem(?BaseModel $item): ?array
    {
        return match (true) {
            $item instanceof DishVariant => [
                'name' => $this->text($item->dish, 'title'),
                'size' => $this->size($item->weight, $item->weight_unit),
                'path' => $this->path($item->dish),
            ],
            $item instanceof Dish => [
                'name' => $this->text($item, 'title'),
                'size' => null,
                'path' => $this->path($item),
            ],
            $item instanceof DishCategory => [
                'name' => $this->text($item, 'title'),
                'size' => null,
                'path' => array_values(array_filter([$this->text($item->menu, 'title')])),
            ],
            $item instanceof DishMenu => ['name' => $this->text($item, 'title'), 'size' => null, 'path' => []],
            $item instanceof RestaurantNote => ['name' => $this->text($item, 'text'), 'size' => null, 'path' => []],
            $item instanceof Restaurant => ['name' => $this->text($item, 'name'), 'size' => null, 'path' => []],
            default => null,
        };
    }

    /**
     * Label of a new item: by its planned values, in its parent.
     *
     * @param MenuVersionChange $change
     * @param BaseModel|null $parent
     *
     * @return array
     */
    protected function ofNewItem(MenuVersionChange $change, ?BaseModel $parent): array
    {
        $values = $change->newValues();
        $locale = ContentLocale::instance()->ofRestaurant($change->version->restaurant_id);

        return match ($change->target_type) {
            'dish-variants' => [
                'name' => $parent ? $this->text($parent, 'title') : null,
                'size' => $this->size($values['weight'] ?? null, $values['weight_unit'] ?? null),
                'path' => $parent instanceof Dish ? $this->path($parent) : [],
            ],
            'dishes' => [
                'name' => $values['title'][$locale] ?? null,
                'size' => null,
                'path' => $parent instanceof DishCategory
                    ? array_values(array_filter([$this->text($parent->menu, 'title'), $this->text($parent, 'title')]))
                    : [],
            ],
            default => ['name' => $values['text'][$locale] ?? null, 'size' => null, 'path' => []],
        };
    }

    /**
     * Menu and category of the dish.
     *
     * @param Dish|null $dish
     *
     * @return string[]
     */
    protected function path(?Dish $dish): array
    {
        return array_values(array_filter([
            $this->text($dish?->menu, 'title'),
            $this->text($dish?->category, 'title'),
        ]));
    }

    /**
     * Size of a dish, if it has one.
     *
     * @param string|null $weight
     * @param string|null $unit
     *
     * @return array|null
     */
    protected function size(?string $weight, ?string $unit): ?array
    {
        return $weight === null || $weight === '' ? null : ['weight' => $weight, 'weight_unit' => $unit];
    }

    /**
     * Text of the model in its default language (or another one, when it has none in it).
     *
     * @param BaseModel|null $model
     * @param string $field
     *
     * @return string|null
     */
    protected function text(?BaseModel $model, string $field): ?string
    {
        if (!$model instanceof TranslatableInterface) {
            return null;
        }

        // another language's, when it has none in the default one
        $texts = array_filter($model->getTranslations($field), fn ($text) => $text !== null && $text !== '');

        return $texts[$model->getDefaultLocale()] ?? (reset($texts) ?: null);
    }
}
