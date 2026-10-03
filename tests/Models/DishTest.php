<?php

namespace Tests\Models;

use App\Models\Dish;
use App\Models\DishMenu;
use App\Models\DishVariant;
use App\Models\Restaurant;
use Tests\TestCase;

/**
 * Class DishTest.
 */
class DishTest extends TestCase
{
    /**
     * Test that only the sizes guests see are loaded with the dish (never deleted ones, not even
     * when deleted records are requested), the admin's sizes have hidden and archived ones too.
     *
     * @return void
     */
    public function testVariantsNeverIncludeDeletedOnes()
    {
        $menu = DishMenu::factory()->withRestaurant(Restaurant::factory()->create())->create();
        $dish = Dish::factory()->withMenu($menu)->create();

        /** @var DishVariant $first */

        $first = $dish->sizes()->sole();
        $live = DishVariant::factory()->withDish($dish)->create(['price' => 100]);
        DishVariant::factory()->withDish($dish)->create(['price' => 120, 'archived' => true]);
        DishVariant::factory()->withDish($dish)->create(['price' => 130, 'is_hidden' => true]);
        DishVariant::factory()->withDish($dish)->create(['price' => 140])->delete();

        $shown = [$live->id, $first->id];
        $this->assertEqualsCanonicalizing($shown, $dish->variants()->pluck('id')->all());

        request()->merge(['deleted' => 'with']);

        $this->assertEqualsCanonicalizing($shown, $dish->variants()->pluck('id')->all());
        $this->assertCount(4, $dish->sizes()->get());
        $this->assertCount(5, $dish->allVariants()->get());
    }
}
