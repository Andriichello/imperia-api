<?php

namespace Tests\Filament;

use App\Enums\UserRole;
use App\Filament\Resources\DishResource;
use App\Filament\Resources\DishResource\Pages\ListDishes;
use App\Filament\Resources\RestaurantResource;
use App\Filament\Resources\RestaurantResource\Pages\EditRestaurant;
use App\Filament\Resources\RestaurantResource\Pages\ListRestaurants;
use App\Models\Dish;
use App\Models\DishMenu;
use App\Models\Restaurant;
use Livewire\Livewire;

/**
 * Class AuthorizationTest.
 *
 * Abilities, which the policies don't have a method for (e.g. `deleteAny`),
 * must still go through the policies' `before()` checks.
 */
class AuthorizationTest extends FilamentTestCase
{
    protected Restaurant $restaurant;

    protected Dish $dish;

    protected function setUp(): void
    {
        parent::setUp();

        $this->restaurant = Restaurant::factory()->create();

        $menu = DishMenu::factory()->withRestaurant($this->restaurant)->create();
        $this->dish = Dish::factory()->withMenu($menu)->create();
    }

    /**
     * Test that managers can't bulk-delete dishes.
     *
     * @return void
     */
    public function testManagersCannotBulkDeleteDishes()
    {
        $this->actingAsStaff(UserRole::Manager, $this->restaurant);

        $this->assertFalse(DishResource::canDeleteAny());

        // calling the hidden action directly doesn't work either
        Livewire::test(ListDishes::class)
            ->assertTableBulkActionHidden('delete')
            ->call('mountTableBulkAction', 'delete', [(string) $this->dish->getKey()])
            ->call('callMountedTableBulkAction');

        $this->assertFalse($this->dish->fresh()->trashed());
    }

    /**
     * Test that admins can still bulk-delete dishes.
     *
     * @return void
     */
    public function testAdminsCanBulkDeleteDishes()
    {
        $this->actingAsStaff(UserRole::Admin, $this->restaurant);

        Livewire::test(ListDishes::class)
            ->assertTableBulkActionVisible('delete')
            ->callTableBulkAction('delete', [$this->dish]);

        $this->assertTrue($this->dish->fresh()->trashed());
    }

    /**
     * Test that managers can't create or delete restaurants.
     *
     * @return void
     */
    public function testManagersCannotCreateOrDeleteRestaurants()
    {
        $this->actingAsStaff(UserRole::Manager, $this->restaurant);

        $this->assertFalse(RestaurantResource::canCreate());
        $this->assertFalse(RestaurantResource::canDelete($this->restaurant));

        Livewire::test(ListRestaurants::class)
            ->assertTableBulkActionHidden('delete')
            ->call('mountTableBulkAction', 'delete', [(string) $this->restaurant->getKey()])
            ->call('callMountedTableBulkAction');

        $this->assertFalse($this->restaurant->fresh()->trashed());
    }

    /**
     * Test that admins of a restaurant can edit it, but not create or delete restaurants.
     *
     * @return void
     */
    public function testRestaurantAdminsCanOnlyEditTheirRestaurant()
    {
        $this->actingAsStaff(UserRole::Admin, $this->restaurant);

        $this->assertTrue(RestaurantResource::canEdit($this->restaurant));
        $this->assertFalse(RestaurantResource::canEdit(Restaurant::factory()->create()));
        $this->assertFalse(RestaurantResource::canCreate());
        $this->assertFalse(RestaurantResource::canDelete($this->restaurant));

        Livewire::test(EditRestaurant::class, ['record' => $this->restaurant->getKey()])
            ->assertActionHidden('delete');
    }
}
