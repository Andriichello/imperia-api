<?php

namespace App\Repositories;

use App\Models\Menu;
use App\Models\Morphs\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Model;

/**
 * Class MenuRepository.
 */
class MenuRepository extends CrudRepository
{
    /**
     * Repository's target model class.
     *
     * @var Model|string
     */
    protected Model|string $model = Menu::class;

    /**
     * Attaches category to menu.
     */
    public function attachCategory(Menu $menu, Category|int $category): void
    {
        $id = is_int($category) ? $category : $category->id;

        /** @phpstan-ignore-next-line */
        $exists = $menu->categories()
            ->where('id', $id)
            ->exists();

        if (!$exists) {
            $menu->categories()->attach($id);
        }
    }

    /**
     * Detaches category from menu.
     */
    public function detachCategory(Menu $menu, Category|int $category): void
    {
        $id = is_int($category) ? $category : $category->id;

        $menu->categories()->detach($id);
    }

    /**
     * Attaches product to menu.
     */
    public function attachProduct(Menu $menu, Product|int $product): void
    {
        $id = is_int($product) ? $product : $product->id;

        /** @phpstan-ignore-next-line */
        $exists = $menu->products()
            ->where('id', $id)
            ->exists();

        if (!$exists) {
            $menu->products()->attach($id);
        }
    }

    /**
     * Detaches product from menu.
     */
    public function detachProduct(Menu $menu, Product|int $product): void
    {
        $id = is_int($product) ? $product : $product->id;

        $menu->products()->detach($id);
    }
}
