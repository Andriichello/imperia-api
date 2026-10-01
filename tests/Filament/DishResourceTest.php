<?php

namespace Tests\Filament;

use App\Enums\UserRole;
use App\Filament\Resources\DishResource\Pages\CreateDish;
use App\Filament\Resources\DishResource\Pages\EditDish;
use App\Filament\Resources\DishResource\Pages\ListDishes;
use App\Models\Dish;
use App\Models\DishCategory;
use App\Models\DishMenu;
use App\Models\Restaurant;
use Filament\Actions\RestoreAction as PageRestoreAction;
use Filament\Forms\Components\Select;
use Filament\Tables\Actions\RestoreAction;
use Livewire\Livewire;

/**
 * Class DishResourceTest.
 */
class DishResourceTest extends FilamentTestCase
{
    /**
     * @var Restaurant
     */
    protected Restaurant $restaurant;

    /**
     * @var DishMenu
     */
    protected DishMenu $menu;

    /**
     * @var DishCategory
     */
    protected DishCategory $category;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->restaurant = Restaurant::factory()->create(['currency' => 'EUR']);
        $this->menu = DishMenu::factory()
            ->withRestaurant($this->restaurant)
            ->create(['title' => 'Kitchen']);
        $this->category = DishCategory::factory()
            ->withMenu($this->menu)
            ->create(['title' => 'Soups']);
    }

    /**
     * Get valid form data for a new dish.
     *
     * @param array $overrides
     *
     * @return array
     */
    protected function dishData(array $overrides = []): array
    {
        return array_merge([
            'menu_id' => $this->menu->id,
            'category_id' => $this->category->id,
            'title' => 'Borscht',
            'price' => 150,
        ], $overrides);
    }

    /**
     * Test that descriptions longer than 255 characters can be saved.
     *
     * @return void
     */
    public function testLongDescriptionsCanBeSaved()
    {
        $this->actingAsStaff();

        $description = str_repeat('Beetroot, cabbage and dill. ', 25);
        $this->assertGreaterThan(255, mb_strlen($description));

        Livewire::test(CreateDish::class)
            ->fillForm($this->dishData(['description' => mb_substr($description, 0, 1020)]))
            ->call('create')
            ->assertHasNoFormErrors();

        $dish = Dish::query()->where('title', 'Borscht')->firstOrFail();
        $this->assertSame(mb_substr($description, 0, 1020), $dish->description);

        $this->menu->update(['description' => $description]);
        $this->category->update(['description' => $description]);

        $this->assertSame($description, $this->menu->fresh()->description);
        $this->assertSame($description, $this->category->fresh()->description);
    }

    /**
     * Test that the form limits match the database columns.
     *
     * @return void
     */
    public function testFormRejectsValuesTheDatabaseCannotStore()
    {
        $this->actingAsStaff();

        Livewire::test(CreateDish::class)
            ->fillForm($this->dishData([
                'badge' => str_repeat('a', 26),
                'price' => -1,
                'calories' => -10,
                'preparation_time' => -5,
            ]))
            ->call('create')
            ->assertHasFormErrors([
                'badge' => 'max',
                'price' => 'min',
                'calories' => 'min',
                'preparation_time' => 'min',
            ]);

        $this->assertSame(0, Dish::query()->count());
    }

    /**
     * Test that deleted dishes are hidden, can be found with
     * the trashed filter and restored, while archived ones stay visible.
     *
     * @return void
     */
    public function testDeletedDishesAreHiddenAndCanBeRestored()
    {
        $this->actingAsStaff();

        $live = Dish::factory()->withMenu($this->menu)->create(['title' => 'Live']);
        $archived = Dish::factory()->withMenu($this->menu)->create(['title' => 'Archived', 'archived' => true]);
        $deleted = Dish::factory()->withMenu($this->menu)->create(['title' => 'Deleted']);
        $deleted->delete();

        Livewire::test(ListDishes::class)
            ->assertCanSeeTableRecords([$live, $archived])
            ->assertCanNotSeeTableRecords([$deleted])
            ->filterTable('trashed', false)
            ->assertCanSeeTableRecords([$deleted])
            ->assertCanNotSeeTableRecords([$live, $archived])
            ->callTableAction(RestoreAction::class, $deleted);

        $this->assertFalse(Dish::query()->withoutGlobalScopes()->findOrFail($deleted->id)->trashed());
    }

    /**
     * Test that a deleted dish can still be opened and restored from its edit page.
     *
     * @return void
     */
    public function testDeletedDishCanBeRestoredFromEditPage()
    {
        $this->actingAsStaff();

        $dish = Dish::factory()->withMenu($this->menu)->create();
        $dish->delete();

        Livewire::test(EditDish::class, ['record' => $dish->getRouteKey()])
            ->assertActionVisible(PageRestoreAction::class)
            ->callAction(PageRestoreAction::class);

        $this->assertFalse(Dish::query()->withoutGlobalScopes()->findOrFail($dish->id)->trashed());
    }

    /**
     * Test that menu options are limited to the user's restaurant
     * and include archived menus.
     *
     * @return void
     */
    public function testMenuOptionsAreScopedToTheUsersRestaurant()
    {
        $archivedMenu = DishMenu::factory()
            ->withRestaurant($this->restaurant)
            ->create(['title' => 'Old kitchen', 'archived' => true]);

        $otherMenu = DishMenu::factory()
            ->withRestaurant(Restaurant::factory()->create())
            ->create(['title' => 'Other kitchen']);

        $this->actingAsStaff(UserRole::Admin, $this->restaurant);

        Livewire::test(CreateDish::class)
            ->assertFormFieldExists('menu_id', function (Select $field) use ($archivedMenu) {
                return $field->getOptions() === [
                    $this->menu->id => 'Kitchen',
                    $archivedMenu->id => 'Old kitchen (archived)',
                ];
            })
            ->fillForm($this->dishData(['menu_id' => $otherMenu->id, 'category_id' => null]))
            ->call('create')
            ->assertHasFormErrors(['menu_id' => 'in']);
    }

    /**
     * Test that admins without a restaurant see menus of all restaurants,
     * prefixed with the restaurant name.
     *
     * @return void
     */
    public function testMenuOptionsShowTheRestaurantForAdmins()
    {
        $other = Restaurant::factory()->create(['name' => 'Bistro']);
        $otherMenu = DishMenu::factory()->withRestaurant($other)->create(['title' => 'Bar']);

        $this->actingAsStaff();

        Livewire::test(CreateDish::class)
            ->assertFormFieldExists('menu_id', function (Select $field) use ($otherMenu) {
                $options = $field->getOptions();

                return $options[$this->menu->id] === $this->restaurant->name . ' · Kitchen'
                    && $options[$otherMenu->id] === 'Bistro · Bar';
            });
    }

    /**
     * Test that the price column uses the currency of the dish's restaurant.
     *
     * @return void
     */
    public function testPriceUsesTheCurrencyOfTheDishRestaurant()
    {
        $usd = Restaurant::factory()->create(['currency' => 'USD']);
        $usdMenu = DishMenu::factory()->withRestaurant($usd)->create();

        $eurDish = Dish::factory()->withMenu($this->menu)->create(['price' => 12.5]);
        $usdDish = Dish::factory()->withMenu($usdMenu)->create(['price' => 9]);

        $this->actingAsStaff();

        Livewire::test(ListDishes::class)
            ->assertTableColumnFormattedStateSet('price', '€12.50', $eurDish)
            ->assertTableColumnFormattedStateSet('price', '$9.00', $usdDish);
    }
}
