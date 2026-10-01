<?php

namespace Tests\Filament;

use App\Enums\UserRole;
use App\Filament\Resources\DishVariantResource\Pages\CreateDishVariant;
use App\Models\Dish;
use App\Models\DishMenu;
use App\Models\DishVariant;
use App\Models\Restaurant;
use Filament\Forms\Components\Select;
use Livewire\Livewire;

/**
 * Class DishVariantResourceTest.
 */
class DishVariantResourceTest extends FilamentTestCase
{
    /**
     * Test that dish options are limited to the user's restaurant,
     * labelled with their menu and include archived dishes.
     *
     * @return void
     */
    public function testDishOptionsAreScopedToTheUsersRestaurant()
    {
        $restaurant = Restaurant::factory()->create();
        $menu = DishMenu::factory()->withRestaurant($restaurant)->create(['title' => 'Kitchen']);
        $soup = Dish::factory()->withMenu($menu)->create(['title' => 'Soup']);
        $stew = Dish::factory()->withMenu($menu)->create(['title' => 'Stew', 'archived' => true]);

        $otherMenu = DishMenu::factory()->withRestaurant(Restaurant::factory()->create())->create();
        $otherDish = Dish::factory()->withMenu($otherMenu)->create();

        $this->actingAsStaff(UserRole::Admin, $restaurant);

        Livewire::test(CreateDishVariant::class)
            ->assertFormFieldExists('dish_id', function (Select $field) use ($soup, $stew) {
                return $field->getOptions() === [
                    $soup->id => 'Kitchen · Soup',
                    $stew->id => 'Kitchen · Stew (archived)',
                ];
            })
            ->fillForm(['dish_id' => $otherDish->id, 'price' => 10])
            ->call('create')
            ->assertHasFormErrors(['dish_id' => 'in']);

        $this->assertSame(0, DishVariant::query()->count());
    }

    /**
     * Test that negative numbers are rejected.
     *
     * @return void
     */
    public function testNegativeNumbersAreRejected()
    {
        $menu = DishMenu::factory()->withRestaurant(Restaurant::factory()->create())->create();
        $dish = Dish::factory()->withMenu($menu)->create();

        $this->actingAsStaff();

        Livewire::test(CreateDishVariant::class)
            ->fillForm(['dish_id' => $dish->id, 'price' => -1, 'calories' => -1, 'preparation_time' => -1])
            ->call('create')
            ->assertHasFormErrors(['price' => 'min', 'calories' => 'min', 'preparation_time' => 'min']);
    }
}
