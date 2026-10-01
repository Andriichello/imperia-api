<?php

namespace Tests\Models;

use App\Models\Dish;
use App\Models\DishCategory;
use App\Models\DishMenu;
use App\Models\Restaurant;
use Tests\TestCase;

/**
 * Class DishMenuTest.
 */
class DishMenuTest extends TestCase
{
    /**
     * Test that deleting a menu soft-deletes all its categories and dishes
     * (archived ones included), and that restoring it brings them back.
     *
     * @return void
     */
    public function testDeleteAndRestoreCascadeToAllChildren()
    {
        $menu = DishMenu::factory()
            ->withRestaurant(Restaurant::factory()->create())
            ->create();

        $categories = [
            DishCategory::factory()->withMenu($menu)->create(),
            DishCategory::factory()->withMenu($menu)->create(['archived' => true]),
        ];

        $dishes = [
            Dish::factory()->withMenu($menu)->withCategory($categories[0])->create(),
            Dish::factory()->withMenu($menu)->withCategory($categories[1])->create(['archived' => true]),
        ];

        $menu->delete();

        foreach (array_merge($categories, $dishes) as $model) {
            $model = $model::query()->withoutGlobalScopes()->findOrFail($model->id);

            $this->assertTrue($model->trashed(), $model::class . " #{$model->id} should be trashed");
        }

        // nothing was deleted permanently
        foreach ($dishes as $dish) {
            $this->assertNotNull(Dish::query()->withoutGlobalScopes()->find($dish->id)?->category_id);
        }

        $menu->restore();

        foreach (array_merge($categories, $dishes) as $model) {
            $model = $model::query()->withoutGlobalScopes()->findOrFail($model->id);

            $this->assertFalse($model->trashed(), $model::class . " #{$model->id} should be restored");
        }
    }
}
