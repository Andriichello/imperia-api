<?php

namespace Tests\Filament;

use App\Enums\UserRole;
use App\Filament\Resources\DishCategoryResource\Pages\ListDishCategories;
use App\Filament\Resources\DishMenuResource\Pages\ListDishMenus;
use App\Filament\Resources\DishResource\Pages\ListDishes;
use App\Models\Dish;
use App\Models\DishCategory;
use App\Models\DishMenu;
use App\Models\Restaurant;
use Livewire\Livewire;

/**
 * Class ReorderingTest.
 *
 * Drag-and-drop ordering of menus, categories and dishes (by popularity, highest first).
 */
class ReorderingTest extends FilamentTestCase
{
    protected Restaurant $restaurant;

    protected DishMenu $menu;

    protected function setUp(): void
    {
        parent::setUp();

        $this->restaurant = Restaurant::factory()->create();
        $this->menu = DishMenu::factory()->withRestaurant($this->restaurant)->create(['popularity' => 1]);
    }

    /**
     * Dishes of the menu with the given popularity values.
     *
     * @param int ...$popularity
     *
     * @return Dish[]
     */
    protected function dishes(int ...$popularity): array
    {
        return array_map(
            fn (int $value) => Dish::factory()->withMenu($this->menu)->create(['popularity' => $value]),
            $popularity
        );
    }

    /**
     * Keys of the records, as the browser sends them.
     *
     * @param array $records
     *
     * @return string[]
     */
    protected function keys(array $records): array
    {
        return array_map(fn ($record) => (string) $record->getKey(), $records);
    }

    /**
     * Test that while reordering, dishes are listed as on the website,
     * and the first one gets the highest popularity.
     *
     * @return void
     */
    public function testDishesAreOrderedLikeOnTheWebsite()
    {
        [$soup, $salad, $steak] = $this->dishes(1, 3, 2);

        $this->actingAsStaff();

        Livewire::test(ListDishes::class)
            ->call('toggleTableReordering')
            ->assertCanSeeTableRecords([$salad, $steak, $soup], inOrder: true)
            ->call('reorderTable', $this->keys([$soup, $steak, $salad]))
            ->assertCanSeeTableRecords([$soup, $steak, $salad], inOrder: true);

        $this->assertEquals(3, $soup->fresh()->popularity);
        $this->assertEquals(2, $steak->fresh()->popularity);
        $this->assertEquals(1, $salad->fresh()->popularity);
    }

    /**
     * Test that restaurant staff can reorder (their queries join the menus),
     * and that records of other restaurants aren't touched.
     *
     * @return void
     */
    public function testRestaurantStaffCanReorderTheirOwnRecords()
    {
        [$soup, $salad] = $this->dishes(1, 2);

        $otherMenu = DishMenu::factory()->withRestaurant(Restaurant::factory()->create())->create();
        $other = Dish::factory()->withMenu($otherMenu)->create(['popularity' => 7]);

        $this->actingAsStaff(UserRole::Admin, $this->restaurant);

        Livewire::test(ListDishes::class)
            ->call('toggleTableReordering')
            ->assertCanSeeTableRecords([$salad, $soup], inOrder: true)
            ->assertCanNotSeeTableRecords([$other])
            ->call('reorderTable', $this->keys([$soup, $other, $salad]));

        $this->assertEquals(3, $soup->fresh()->popularity);
        $this->assertEquals(1, $salad->fresh()->popularity);
        $this->assertEquals(7, $other->fresh()->popularity);
    }

    /**
     * Test that menus and categories can be reordered too.
     *
     * @return void
     */
    public function testMenusAndCategoriesCanBeReordered()
    {
        $lunch = DishMenu::factory()->withRestaurant($this->restaurant)->create(['popularity' => 5]);
        $soups = DishCategory::factory()->withMenu($this->menu)->create(['popularity' => 1]);
        $salads = DishCategory::factory()->withMenu($this->menu)->create(['popularity' => 2]);

        $this->actingAsStaff(UserRole::Admin, $this->restaurant);

        Livewire::test(ListDishMenus::class)
            ->call('toggleTableReordering')
            ->assertCanSeeTableRecords([$lunch, $this->menu], inOrder: true)
            ->call('reorderTable', $this->keys([$this->menu, $lunch]));

        $this->assertEquals(2, $this->menu->fresh()->popularity);
        $this->assertEquals(1, $lunch->fresh()->popularity);

        Livewire::test(ListDishCategories::class)
            ->call('toggleTableReordering')
            ->assertCanSeeTableRecords([$salads, $soups], inOrder: true)
            ->call('reorderTable', $this->keys([$soups, $salads]));

        $this->assertEquals(2, $soups->fresh()->popularity);
        $this->assertEquals(1, $salads->fresh()->popularity);
    }

    /**
     * Test that managers can't reorder.
     *
     * @return void
     */
    public function testManagersCannotReorder()
    {
        [$soup, $salad] = $this->dishes(1, 2);

        $this->actingAsStaff(UserRole::Manager, $this->restaurant);

        Livewire::test(ListDishes::class)
            ->call('reorderTable', $this->keys([$soup, $salad]));

        $this->assertEquals(1, $soup->fresh()->popularity);
        $this->assertEquals(2, $salad->fresh()->popularity);
    }
}
