<?php

namespace App\Repositories\Editor;

use App\Models\Dish;
use App\Models\DishCategory;
use App\Models\DishMenu;
use App\Models\Restaurant;
use App\Models\Scopes\ArchivedScope;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

/**
 * Class MenuEditorRepository.
 *
 * Changes of menus: texts, visibility, and the order of menus and their categories.
 */
class MenuEditorRepository extends EditorRepository
{
    /**
     * MenuEditorRepository constructor.
     *
     * @param CategoryEditorRepository $categories
     * @param DishEditorRepository $dishes
     */
    public function __construct(
        protected CategoryEditorRepository $categories,
        protected DishEditorRepository $dishes,
    ) {
    }

    /**
     * Create a menu at the end of the restaurant's menus.
     *
     * @param Restaurant $restaurant
     * @param array $data
     *
     * @return DishMenu
     */
    public function create(Restaurant $restaurant, array $data): DishMenu
    {
        $menu = new DishMenu(['restaurant_id' => $restaurant->id]);

        $menu->popularity = $this->popularityAtTheEnd(
            DishMenu::query()->withoutGlobalScopes()->where('restaurant_id', $restaurant->id)
        );

        return $this->update($menu, $data);
    }

    /**
     * Update texts and visibility of the menu. What's left out of the data stays as it is.
     *
     * @param DishMenu $menu
     * @param array $data
     *
     * @return DishMenu
     */
    public function update(DishMenu $menu, array $data): DishMenu
    {
        $this->translate($menu, $data, ['title', 'description']);

        $menu->fill(Arr::only($data, ['is_hidden']));
        $menu->save();

        return $menu;
    }

    /**
     * Order menus of the restaurant and the categories in each of them. A category listed
     * under another menu is moved there, with its dishes.
     *
     * @param Restaurant $restaurant
     * @param array $menus `[['id' => 1, 'categories' => [3, 4]], ...]`
     *
     * @return void
     */
    public function orderMenus(Restaurant $restaurant, array $menus): void
    {
        DB::transaction(function () use ($menus) {
            $this->order(DishMenu::class, Arr::pluck($menus, 'id'));

            foreach ($menus as $menu) {
                $this->order(DishCategory::class, $menu['categories'], ['menu_id' => $menu['id']]);

                // dishes of the categories, which were moved to the menu
                Dish::query()
                    ->withoutGlobalScopes()
                    ->whereIn('category_id', $menu['categories'])
                    ->where('menu_id', '!=', $menu['id'])
                    ->update(['menu_id' => $menu['id']]);
            }
        });

        $this->forgetWebsite($restaurant->id);
    }

    /**
     * Copy the menu with its categories, dishes and photos. The copy is hidden and goes
     * at the end; what's inside keeps its states. It gets no slug.
     *
     * @param DishMenu $menu
     *
     * @return DishMenu
     */
    public function duplicate(DishMenu $menu): DishMenu
    {
        return DB::transaction(function () use ($menu) {
            /** @var DishMenu $copy */
            $copy = $menu->replicate(['slug']);
            $copy->is_hidden = true;
            $copy->archived = false;
            $copy->archived_at = null;
            $copy->popularity = $this->popularityAtTheEnd(
                DishMenu::query()->withoutGlobalScopes()->where('restaurant_id', $menu->restaurant_id)
            );
            $copy->save();

            $copy->media()->attach($menu->media()
                ->pluck('mediables.order', 'media.id')
                ->map(fn ($order) => ['order' => $order])
                ->all());

            /** @var DishCategory $category */
            foreach ($menu->categories()->withoutGlobalScope(ArchivedScope::class)->get() as $category) {
                $this->categories->copy($category, ['menu_id' => $copy->id]);
            }

            // dishes without a category aren't shown, but they're the menu's too
            $uncategorized = $menu->dishes()
                ->withoutGlobalScope(ArchivedScope::class)
                ->whereNull('category_id')
                ->get();

            /** @var Dish $dish */
            foreach ($uncategorized as $dish) {
                $this->dishes->copy($dish, ['menu_id' => $copy->id]);
            }

            return $copy;
        });
    }
}
