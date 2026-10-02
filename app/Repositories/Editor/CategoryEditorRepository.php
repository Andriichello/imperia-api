<?php

namespace App\Repositories\Editor;

use App\Models\Dish;
use App\Models\DishCategory;
use App\Models\DishMenu;
use App\Models\Scopes\ArchivedScope;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

/**
 * Class CategoryEditorRepository.
 *
 * Changes of categories: texts, visibility, order of their dishes and the menu they're in.
 */
class CategoryEditorRepository extends EditorRepository
{
    /**
     * CategoryEditorRepository constructor.
     *
     * @param DishEditorRepository $dishes
     */
    public function __construct(protected DishEditorRepository $dishes)
    {
    }

    /**
     * Create a category at the end of the menu.
     *
     * @param DishMenu $menu
     * @param array $data
     *
     * @return DishCategory
     */
    public function create(DishMenu $menu, array $data): DishCategory
    {
        $category = new DishCategory(['menu_id' => $menu->id]);

        $category->popularity = $this->popularityAtTheEnd(
            DishCategory::query()->withoutGlobalScopes()->where('menu_id', $menu->id)
        );

        return $this->update($category, $data);
    }

    /**
     * Update texts and visibility of the category, and the order of its dishes.
     * What's left out of the data stays as it is.
     *
     * @param DishCategory $category
     * @param array $data
     *
     * @return DishCategory
     */
    public function update(DishCategory $category, array $data): DishCategory
    {
        return DB::transaction(function () use ($category, $data) {
            $this->translate($category, $data, ['title', 'description']);

            $category->fill(Arr::only($data, ['is_hidden']));
            $category->save();

            if (array_key_exists('dishes', $data)) {
                $this->order(Dish::class, $data['dishes']);
            }

            return $category;
        });
    }

    /**
     * Move the category with its dishes to the end of another menu.
     *
     * @param DishCategory $category
     * @param DishMenu $menu
     *
     * @return DishCategory
     */
    public function move(DishCategory $category, DishMenu $menu): DishCategory
    {
        if ($category->menu_id === $menu->id) {
            return $category;
        }

        return DB::transaction(function () use ($category, $menu) {
            $category->popularity = $this->popularityAtTheEnd(
                DishCategory::query()->withoutGlobalScopes()->where('menu_id', $menu->id)
            );
            $category->menu_id = $menu->id;
            $category->save();

            Dish::query()
                ->withoutGlobalScopes()
                ->where('category_id', $category->id)
                ->update(['menu_id' => $menu->id]);

            return $category;
        });
    }

    /**
     * Copy the category as a hidden draft at the end of its menu, with its dishes (they keep their states).
     *
     * @param DishCategory $category
     *
     * @return DishCategory
     */
    public function duplicate(DishCategory $category): DishCategory
    {
        return $this->copy($category, [
            'is_hidden' => true,
            'archived' => false,
            'archived_at' => null,
            'popularity' => $this->popularityAtTheEnd(
                DishCategory::query()->withoutGlobalScopes()->where('menu_id', $category->menu_id)
            ),
        ]);
    }

    /**
     * Copy the category with its dishes and photos, keeping its state (e.g. with its menu).
     * The copy gets no slug.
     *
     * @param DishCategory $category
     * @param array $attributes of the copy (e.g. its menu)
     *
     * @return DishCategory
     */
    public function copy(DishCategory $category, array $attributes = []): DishCategory
    {
        return DB::transaction(function () use ($category, $attributes) {
            /** @var DishCategory $copy */
            $copy = $category->replicate(['slug']);
            $copy->forceFill($attributes);
            $copy->save();

            $copy->media()->attach($category->media()
                ->pluck('mediables.order', 'media.id')
                ->map(fn ($order) => ['order' => $order])
                ->all());

            /** @var Dish $dish */
            foreach ($category->dishes()->withoutGlobalScope(ArchivedScope::class)->get() as $dish) {
                $this->dishes->copy($dish, [
                    'menu_id' => $copy->menu_id,
                    'category_id' => $copy->id,
                ]);
            }

            return $copy;
        });
    }
}
