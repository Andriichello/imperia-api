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
     * Test that deleted variants are never loaded with the dish,
     * not even when deleted records are requested.
     *
     * @return void
     */
    public function testVariantsNeverIncludeDeletedOnes()
    {
        $menu = DishMenu::factory()->withRestaurant(Restaurant::factory()->create())->create();
        $dish = Dish::factory()->withMenu($menu)->create();

        $live = DishVariant::factory()->withDish($dish)->create(['price' => 100]);
        DishVariant::factory()->withDish($dish)->create(['price' => 120, 'archived' => true]);
        DishVariant::factory()->withDish($dish)->create(['price' => 140])->delete();

        $this->assertSame([$live->id], $dish->variants()->pluck('id')->all());

        request()->merge(['deleted' => 'with']);

        $this->assertSame([$live->id], $dish->variants()->pluck('id')->all());
        $this->assertCount(3, $dish->allVariants()->get());
    }
}
