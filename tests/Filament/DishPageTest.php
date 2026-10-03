<?php

namespace Tests\Filament;

use App\Enums\UserRole;
use App\Filament\Resources\DishCategoryResource;
use App\Filament\Resources\DishResource\Pages\CreateDish;
use App\Filament\Resources\DishResource\Pages\EditDish;
use App\Filament\Resources\DishResource\Pages\ListDishes;
use App\Models\Dish;
use App\Models\DishCategory;
use App\Models\DishMenu;
use App\Models\DishVariant;
use App\Models\MenuVersion;
use App\Models\MenuVersionChange;
use App\Models\Restaurant;
use Database\Factories\Morphs\MediaFactory;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

/**
 * Class DishPageTest.
 *
 * Flags, filters, the live toggle and duplicating of dishes.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DishPageTest extends FilamentTestCase
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

        $this->restaurant = Restaurant::factory()->create();
        $this->menu = DishMenu::factory()->withRestaurant($this->restaurant)->create(['title' => 'Kitchen']);
        $this->category = DishCategory::factory()->withMenu($this->menu)->create(['title' => 'Soups']);
    }

    /**
     * Test that tags, hotness and allergens are saved together as flags.
     *
     * @return void
     */
    public function testFlagsAreSavedFromSeparateInputs()
    {
        $this->actingAsStaff();

        Livewire::test(CreateDish::class)
            ->fillForm([
                'menu_id' => $this->menu->id,
                'title' => 'Borscht',
                'price' => 150,
                'flag_tags' => ['vegan', 'high-protein'],
                'flag_hotness' => 'medium-hotness',
                'flag_allergens' => ['alg-celery', 'alg-wheat'],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        /** @var Dish $dish */
        $dish = Dish::query()->sole();

        $this->assertEqualsCanonicalizing(
            ['vegan', 'high-protein', 'medium-hotness', 'alg-celery', 'alg-wheat'],
            $dish->flags
        );
    }

    /**
     * Test that the inputs are filled from the flags, and that saving keeps
     * the order of the remaining flags and the ones the inputs don't know.
     *
     * @return void
     */
    public function testFlagsAreSplitAndKeptOnEdit()
    {
        $this->actingAsStaff();

        $dish = Dish::factory()->withMenu($this->menu)->create([
            'flags' => ['alg-nuts', 'legacy-flag', 'vegan', 'hotness', 'alg-milk'],
        ]);

        Livewire::test(EditDish::class, ['record' => $dish->getRouteKey()])
            ->assertFormSet([
                'flag_tags' => ['vegan'],
                'flag_hotness' => 'hotness',
                'flag_allergens' => ['alg-nuts', 'alg-milk'],
            ])
            ->fillForm([
                'flag_hotness' => 'extreme-hotness',
                'flag_allergens' => ['alg-nuts', 'alg-milk', 'alg-fish'],
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame(
            ['alg-nuts', 'legacy-flag', 'vegan', 'alg-milk', 'extreme-hotness', 'alg-fish'],
            $dish->fresh()->flags
        );

        Livewire::test(EditDish::class, ['record' => $dish->getRouteKey()])
            ->fillForm(['flag_tags' => [], 'flag_hotness' => null])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame(['alg-nuts', 'legacy-flag', 'alg-milk', 'alg-fish'], $dish->fresh()->flags);
    }

    /**
     * Test that saving without touching the flags doesn't change them.
     *
     * @return void
     */
    public function testUnchangedFlagsStayTheSame()
    {
        $this->actingAsStaff();

        $dish = Dish::factory()->withMenu($this->menu)->create([
            'flags' => ['alg-milk', 'low-hotness', 'vegetarian'],
        ]);

        Livewire::test(EditDish::class, ['record' => $dish->getRouteKey()])
            ->fillForm(['price' => 99])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame(['alg-milk', 'low-hotness', 'vegetarian'], $dish->fresh()->flags);
    }

    /**
     * Test the restaurant, menu, flags and status filters.
     *
     * @return void
     */
    public function testDishesCanBeFiltered()
    {
        $lunch = DishMenu::factory()->withRestaurant($this->restaurant)->create(['title' => 'Lunch']);
        $bistro = DishMenu::factory()->withRestaurant(Restaurant::factory()->create())->create();

        $soup = Dish::factory()->withMenu($this->menu)->create(['flags' => ['vegan', 'alg-celery']]);
        $salad = Dish::factory()->withMenu($lunch)->create(['flags' => ['alg-nuts'], 'is_hidden' => true]);
        $steak = Dish::factory()->withMenu($bistro)->create(['flags' => []]);

        $this->actingAsStaff();

        Livewire::test(ListDishes::class)
            ->assertCanSeeTableRecords([$soup, $salad, $steak])
            ->filterTable('restaurant', $this->restaurant->id)
            ->assertCanSeeTableRecords([$soup, $salad])
            ->assertCanNotSeeTableRecords([$steak])
            ->resetTableFilters()
            ->filterTable('menu', $lunch->id)
            ->assertCanSeeTableRecords([$salad])
            ->assertCanNotSeeTableRecords([$soup, $steak])
            ->resetTableFilters()
            ->filterTable('flags', ['alg-nuts', 'alg-celery'])
            ->assertCanSeeTableRecords([$soup, $salad])
            ->assertCanNotSeeTableRecords([$steak])
            ->resetTableFilters()
            ->filterTable('live', true)
            ->assertCanSeeTableRecords([$soup, $steak])
            ->assertCanNotSeeTableRecords([$salad])
            ->filterTable('live', false)
            ->assertCanSeeTableRecords([$salad])
            ->assertCanNotSeeTableRecords([$soup, $steak]);
    }

    /**
     * Test that dishes can be filtered by category, picked from the categories grouped by menu.
     *
     * @return void
     */
    public function testDishesCanBeFilteredByCategory()
    {
        $salads = DishCategory::factory()->withMenu($this->menu)->create(['title' => 'Salads']);
        $soup = Dish::factory()->withMenu($this->menu)->withCategory($this->category)->create();
        $salad = Dish::factory()->withMenu($this->menu)->withCategory($salads)->create();

        $this->actingAsStaff(UserRole::Admin, $this->restaurant);

        $this->assertSame(
            ['Kitchen' => [$salads->id => 'Salads', $this->category->id => 'Soups']],
            DishCategoryResource::getGroupedSelectOptions()
        );

        Livewire::test(ListDishes::class)
            ->filterTable('category', $salads->id)
            ->assertCanSeeTableRecords([$salad])
            ->assertCanNotSeeTableRecords([$soup]);
    }

    /**
     * Test that restaurant-bound users don't get the restaurant filter.
     *
     * @return void
     */
    public function testRestaurantFilterIsHiddenForRestaurantStaff()
    {
        $this->actingAsStaff(UserRole::Admin, $this->restaurant);

        Livewire::test(ListDishes::class)
            ->assertTableFilterHidden('restaurant')
            ->assertTableFilterVisible('menu');
    }

    /**
     * Test that the live toggle hides and publishes dishes.
     *
     * @return void
     */
    public function testLiveToggleHidesAndPublishes()
    {
        $dish = Dish::factory()->withMenu($this->menu)->create(['is_hidden' => false]);

        $this->actingAsStaff(UserRole::Admin, $this->restaurant);

        Livewire::test(ListDishes::class)
            ->assertTableColumnStateSet('is_hidden', true, $dish->getKey())
            ->call('updateTableColumnState', 'is_hidden', (string) $dish->getKey(), false);

        $this->assertTrue($dish->fresh()->is_hidden);

        Livewire::test(ListDishes::class)
            ->assertTableColumnStateSet('is_hidden', false, $dish->getKey())
            ->call('updateTableColumnState', 'is_hidden', (string) $dish->getKey(), true);

        $this->assertFalse($dish->fresh()->is_hidden);
    }

    /**
     * Test that managers can't use the live toggle.
     *
     * @return void
     */
    public function testManagersCannotToggleLive()
    {
        $dish = Dish::factory()->withMenu($this->menu)->create(['is_hidden' => false]);

        $this->actingAsStaff(UserRole::Manager, $this->restaurant);

        Livewire::test(ListDishes::class)
            ->assertTableColumnStateSet('is_hidden', true, $dish->getKey())
            ->call('updateTableColumnState', 'is_hidden', (string) $dish->getKey(), false);

        $this->assertFalse($dish->fresh()->is_hidden);
    }

    /**
     * Test that a dish is duplicated as a hidden copy with its sizes and images,
     * without its slug, scheduled changes or the old menu reference.
     *
     * @return void
     */
    public function testDishCanBeDuplicated()
    {
        $lunch = DishMenu::factory()->withRestaurant($this->restaurant)->create(['title' => 'Lunch']);
        $lunchSoups = DishCategory::factory()->withMenu($lunch)->create();

        $dish = Dish::factory()->withMenu($this->menu)->withCategory($this->category)->create([
            'title' => 'Borscht',
            'slug' => 'borscht',
            'price' => 150,
            'flags' => ['vegan', 'alg-celery'],
        ]);
        $dish->setToJson('metadata', 'copied_from', ['type' => 'products', 'id' => 1, 'menu_id' => 1]);
        $dish->save();

        $small = DishVariant::factory()->withDish($dish)->create(['price' => 100, 'weight' => '300']);
        $old = DishVariant::factory()->withDish($dish)->create(['price' => 120, 'archived' => true]);
        DishVariant::factory()->withDish($dish)->create(['price' => 130])->delete();

        $photos = MediaFactory::new()->count(2)->create();
        foreach ($photos as $index => $photo) {
            DB::table('mediables')->insert([
                'media_id' => $photo->id,
                'mediable_id' => $dish->id,
                'mediable_type' => $dish->getMorphClass(),
                'order' => 2 - $index,
            ]);
        }

        $version = MenuVersion::factory()->withRestaurant($this->restaurant)->scheduled()->create();
        MenuVersionChange::factory()->inVersion($version)
            ->changing($dish, ['title' => ['en' => 'Beet soup']])
            ->create();

        $this->actingAsStaff(UserRole::Admin, $this->restaurant);

        Livewire::test(ListDishes::class)
            ->mountTableAction('duplicate', $dish)
            ->assertTableActionDataSet([
                'menu_id' => $this->menu->id,
                'category_id' => $this->category->id,
                'title' => 'Borscht (copy)',
            ])
            ->setTableActionData([
                'menu_id' => $lunch->id,
                'category_id' => $lunchSoups->id,
                'title' => 'Lunch borscht',
            ])
            ->callMountedTableAction()
            ->assertHasNoTableActionErrors()
            ->assertNotified('The dish was duplicated');

        /** @var Dish $copy */
        $copy = Dish::query()->withoutGlobalScopes()->where('title->en', 'Lunch borscht')->sole();

        $this->assertSame($lunch->id, $copy->menu_id);
        $this->assertSame($lunchSoups->id, $copy->category_id);
        $this->assertTrue($copy->is_hidden);
        $this->assertFalse((bool) $copy->archived);
        $this->assertNull($copy->slug);
        // the cheapest of its sizes, which guests see
        $this->assertEquals(100, $copy->price);
        $this->assertSame(['vegan', 'alg-celery'], $copy->flags);
        $this->assertNull($copy->getFromJson('metadata', 'copied_from'));

        // the original is untouched
        $this->assertSame('borscht', $dish->fresh()->slug);
        $this->assertNotNull($dish->fresh()->getFromJson('metadata', 'copied_from'));

        // archived sizes are copied (still archived), deleted ones aren't
        $variants = DishVariant::query()->withoutGlobalScopes()->where('dish_id', $copy->id)->orderBy('price')->get();
        $this->assertSame([100.0, 120.0, 150.0], $variants->pluck('price')->all());
        $this->assertSame([false, true, false], $variants->pluck('archived')->all());
        $this->assertSame('300', $variants[0]->weight);
        $this->assertNotContains($small->id, $variants->pluck('id'));
        $this->assertNotContains($old->id, $variants->pluck('id'));

        // the same images in the same order
        $this->assertSame(
            $dish->media()->pluck('media.id')->all(),
            $copy->media()->pluck('media.id')->all()
        );
        $this->assertCount(2, $copy->media);

        $this->assertSame(0, $copy->scheduledChanges()->count());
    }

    /**
     * Test that the edit page can duplicate too and opens the copy.
     *
     * @return void
     */
    public function testDishCanBeDuplicatedFromEditPage()
    {
        $dish = Dish::factory()->withMenu($this->menu)->create(['title' => 'Borscht']);

        $this->actingAsStaff();

        $page = Livewire::test(EditDish::class, ['record' => $dish->getRouteKey()])
            ->callAction('duplicate')
            ->assertHasNoActionErrors();

        $copy = Dish::query()->withoutGlobalScopes()->where('title->en', 'Borscht (copy)')->sole();

        $page->assertRedirect(EditDish::getUrl(['record' => $copy]));
    }

    /**
     * Test that managers (who can't open the edit page either) can't duplicate dishes.
     *
     * @return void
     */
    public function testManagersCannotDuplicate()
    {
        $dish = Dish::factory()->withMenu($this->menu)->create();

        $this->actingAsStaff(UserRole::Manager, $this->restaurant);

        Livewire::test(ListDishes::class)
            ->assertTableActionHidden('duplicate', $dish);
    }
}
