<?php

namespace Tests\Filament;

use App\Filament\Resources\DishCategoryResource\Pages\EditDishCategory;
use App\Filament\Resources\DishMenuResource\Pages\EditDishMenu;
use App\Filament\Resources\DishResource\Pages\CreateDish;
use App\Filament\Resources\DishResource\Pages\EditDish;
use App\Models\Dish;
use App\Models\DishCategory;
use App\Models\DishMenu;
use App\Models\Restaurant;
use App\Models\Scopes\ArchivedScope;
use Livewire\Livewire;

/**
 * Class LiveFieldsTest.
 *
 * The "Live" toggle in the forms, which saves the opposite into `archived`.
 */
class LiveFieldsTest extends FilamentTestCase
{
    protected DishMenu $menu;

    protected function setUp(): void
    {
        parent::setUp();

        $this->menu = DishMenu::factory()
            ->withRestaurant(Restaurant::factory()->create())
            ->create(['archived' => false]);
    }

    /**
     * Test that new dishes are live, unless the toggle is turned off.
     *
     * @return void
     */
    public function testNewDishesAreLiveByDefault()
    {
        $this->actingAsStaff();

        Livewire::test(CreateDish::class)
            ->assertFormSet(['live' => true])
            ->fillForm(['menu_id' => $this->menu->id, 'title' => 'Borscht', 'price' => 150])
            ->call('create')
            ->assertHasNoFormErrors();

        Livewire::test(CreateDish::class)
            ->fillForm(['menu_id' => $this->menu->id, 'title' => 'Okroshka', 'price' => 120, 'live' => false])
            ->call('create')
            ->assertHasNoFormErrors();

        $dishes = Dish::query()->withoutGlobalScope(ArchivedScope::class)->pluck('archived', 'title');

        $this->assertFalse((bool) $dishes['Borscht']);
        $this->assertTrue((bool) $dishes['Okroshka']);
    }

    /**
     * Test that the toggle shows whether a dish is live, and archives or publishes it.
     *
     * @return void
     */
    public function testDishCanBeArchivedAndPublished()
    {
        $dish = Dish::factory()->withMenu($this->menu)->create(['archived' => true]);

        $this->actingAsStaff();

        Livewire::test(EditDish::class, ['record' => $dish->getRouteKey()])
            ->assertFormSet(['live' => false])
            ->fillForm(['live' => true])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertFalse((bool) $dish->fresh()->archived);

        Livewire::test(EditDish::class, ['record' => $dish->getRouteKey()])
            ->assertFormSet(['live' => true])
            ->fillForm(['live' => false])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertTrue((bool) $dish->fresh()->archived);
    }

    /**
     * Test that menus and categories have the toggle too.
     *
     * @return void
     */
    public function testMenusAndCategoriesCanBeArchived()
    {
        $category = DishCategory::factory()->withMenu($this->menu)->create(['archived' => false]);

        $this->actingAsStaff();

        Livewire::test(EditDishMenu::class, ['record' => $this->menu->getRouteKey()])
            ->assertFormSet(['live' => true])
            ->fillForm(['live' => false])
            ->call('save')
            ->assertHasNoFormErrors();

        Livewire::test(EditDishCategory::class, ['record' => $category->getRouteKey()])
            ->assertFormSet(['live' => true])
            ->fillForm(['live' => false])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertTrue((bool) $this->menu->fresh()->archived);
        $this->assertTrue((bool) $category->fresh()->archived);
    }
}
