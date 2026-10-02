<?php

namespace App\Repositories\Editor;

use App\Models\Dish;
use App\Models\DishCategory;
use App\Models\DishVariant;
use App\Models\Scopes\ArchivedScope;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

/**
 * Class DishEditorRepository.
 *
 * Changes of dishes: texts, visibility, flags, sizes and photos.
 */
class DishEditorRepository extends EditorRepository
{
    /**
     * Attributes of a dish's size (the dish's own ones, or a variant's).
     *
     * @var string[]
     */
    protected const SIZE = ['price', 'weight', 'weight_unit', 'calories', 'preparation_time'];

    /**
     * Create a dish at the end of the category.
     *
     * @param DishCategory $category
     * @param array $data
     *
     * @return Dish
     */
    public function create(DishCategory $category, array $data): Dish
    {
        $dish = new Dish([
            'menu_id' => $category->menu_id,
            'category_id' => $category->id,
        ]);

        $dish->popularity = $this->popularityAtTheEnd(
            Dish::query()->withoutGlobalScopes()->where('category_id', $category->id)
        );

        return $this->update($dish, $data);
    }

    /**
     * Update the dish: texts, visibility, flags, sizes (replaced) and photos.
     * What's left out of the data stays as it is.
     *
     * @param Dish $dish
     * @param array $data
     *
     * @return Dish
     */
    public function update(Dish $dish, array $data): Dish
    {
        return DB::transaction(function () use ($dish, $data) {
            $this->translate($dish, $data, ['title', 'description', 'badge']);

            $dish->fill(Arr::only($data, ['is_hidden', 'flags']));

            // the first size is the dish itself, the others are its variants
            $sizes = array_map(fn (array $size) => $this->sizeValues($size), array_values($data['sizes'] ?? []));
            $own = array_shift($sizes);

            if ($own) {
                $dish->fill(Arr::except($own, 'id'));
            }

            $dish->save();

            if ($own) {
                $this->setVariants($dish, $sizes);
            }

            if (array_key_exists('media', $data)) {
                $dish->setMedia(...$data['media']);
            }

            return $dish;
        });
    }

    /**
     * Replace variants of the dish with the given sizes: kept variants are updated, others are
     * deleted or added (archived variants aren't touched).
     *
     * @param Dish $dish
     * @param array $sizes
     *
     * @return void
     */
    protected function setVariants(Dish $dish, array $sizes): void
    {
        $existing = $dish->variants()->get()->keyBy('id');
        $kept = [];

        foreach ($sizes as $size) {
            /** @var DishVariant $variant */
            $variant = $existing->get($size['id'] ?? 0) ?? new DishVariant(['dish_id' => $dish->id]);
            $variant->fill(Arr::except($size, 'id'));
            $variant->save();

            $kept[] = $variant->id;
        }

        $existing->except($kept)->each(fn ($variant) => $variant->delete());
    }

    /**
     * Values of a size to save: the weight is stored as text (e.g. "300" or "0.5").
     *
     * @param array $size
     *
     * @return array
     */
    protected function sizeValues(array $size): array
    {
        $values = Arr::only($size, ['id', ...static::SIZE]);

        $weight = $values['weight'] ?? null;
        $values['weight'] = $weight === null || $weight === '' ? null : (string) ($weight + 0);
        $values['weight_unit'] = $values['weight'] === null ? null : ($values['weight_unit'] ?? null);

        return $values;
    }

    /**
     * Move the dish to the end of another category (in any menu of the restaurant).
     *
     * @param Dish $dish
     * @param DishCategory $category
     *
     * @return Dish
     */
    public function move(Dish $dish, DishCategory $category): Dish
    {
        if ($dish->category_id !== $category->id) {
            $dish->popularity = $this->popularityAtTheEnd(
                Dish::query()->withoutGlobalScopes()->where('category_id', $category->id)
            );
        }

        $dish->menu_id = $category->menu_id;
        $dish->category_id = $category->id;
        $dish->save();

        return $dish;
    }

    /**
     * Copy the dish as a hidden draft (in the same category, if not given another one).
     *
     * @param Dish $dish
     * @param array $attributes of the copy (e.g. its menu, category and title)
     *
     * @return Dish
     */
    public function duplicate(Dish $dish, array $attributes = []): Dish
    {
        return $this->copy($dish, [
            ...$attributes,
            'is_hidden' => true,
            'archived' => false,
            'archived_at' => null,
        ]);
    }

    /**
     * Copy the dish with its variants (archived ones included) and photos, keeping its state
     * (e.g. with its menu or category). The copy gets no slug, scheduled changes aren't copied.
     *
     * @param Dish $dish
     * @param array $attributes of the copy (e.g. its menu and category)
     *
     * @return Dish
     */
    public function copy(Dish $dish, array $attributes = []): Dish
    {
        return DB::transaction(function () use ($dish, $attributes) {
            /** @var Dish $copy */
            $copy = $dish->replicate(['slug', 'scheduled_changes_count']);
            $copy->forceFill(Arr::only($attributes, ['is_hidden', 'archived', 'archived_at']));
            $copy->fill(Arr::except($attributes, ['is_hidden', 'archived', 'archived_at']));

            // the copy isn't a copy of the old menu (see `dishes:copy-old-menu`)
            $copy->setJson('metadata', Arr::except($copy->getJson('metadata'), 'copied_from'));
            $copy->save();

            /** @var DishVariant $variant */
            foreach ($dish->variants()->withoutGlobalScope(ArchivedScope::class)->get() as $variant) {
                $variant->replicate()
                    ->fill(['dish_id' => $copy->id])
                    ->save();
            }

            $copy->media()->attach($dish->media()
                ->pluck('mediables.order', 'media.id')
                ->map(fn ($order) => ['order' => $order])
                ->all());

            return $copy;
        });
    }
}
