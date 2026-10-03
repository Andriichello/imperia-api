<?php

namespace Tests\Filament;

use App\Enums\UserRole;
use App\Filament\Resources\DishResource\Pages\EditDish;
use App\Filament\Resources\DishResource\Pages\ListDishes;
use App\Filament\Resources\DishResource\RelationManagers\VariantsRelationManager;
use App\Models\Dish;
use App\Models\DishMenu;
use App\Models\DishVariant;
use App\Models\MenuVersion;
use App\Models\MenuVersionChange;
use App\Models\Restaurant;
use Carbon\Carbon;
use Livewire\Livewire;

/**
 * Class SchedulePriceChangeTest.
 *
 * The bulk action, which schedules price changes of dishes (all their sizes) and sizes,
 * as one version per restaurant.
 */
class SchedulePriceChangeTest extends FilamentTestCase
{
    protected Restaurant $restaurant;

    protected Dish $soup;

    protected Dish $salad;

    protected function setUp(): void
    {
        parent::setUp();

        $this->restaurant = Restaurant::factory()->create(['timezone' => 'Europe/Kyiv']);

        $menu = DishMenu::factory()->withRestaurant($this->restaurant)->create();
        $this->soup = Dish::factory()->withMenu($menu)->create(['price' => 100]);
        $this->salad = Dish::factory()->withMenu($menu)->create(['price' => 149]);
    }

    /**
     * Next Monday at 09:00, as entered in the form (in the restaurant's timezone).
     *
     * @return string
     */
    protected function nextMonday(): string
    {
        return Carbon::now()->next(Carbon::MONDAY)->setTime(9, 0)->toDateTimeString();
    }

    /**
     * The only scheduled change of the size (the first size of a dish).
     *
     * @param Dish|DishVariant $record
     *
     * @return MenuVersionChange
     */
    protected function changeOf(Dish|DishVariant $record): MenuVersionChange
    {
        /** @var DishVariant $size */
        $size = $record instanceof Dish ? $record->sizes()->first() : $record;

        /** @var MenuVersionChange $change */
        $change = $size->scheduledChanges()->sole();

        return $change;
    }

    /**
     * The new price of the size in its scheduled change.
     *
     * @param Dish|DishVariant $record
     *
     * @return float
     */
    protected function newPrice(Dish|DishVariant $record): float
    {
        return $this->changeOf($record)->fields['price']['new'];
    }

    /**
     * Test that prices can be changed by a percent, rounded,
     * at the given time in the restaurant's timezone.
     *
     * @return void
     */
    public function testPricesChangeByPercent()
    {
        $this->actingAsStaff(UserRole::Admin, $this->restaurant);

        Livewire::test(ListDishes::class)
            ->callTableBulkAction('schedulePriceChange', [$this->soup, $this->salad], data: [
                'goes_live_at' => $this->nextMonday(),
                'mode' => 'percent',
                'value' => 10,
                'round_to' => 1,
            ])
            ->assertHasNoTableBulkActionErrors()
            ->assertNotified('Scheduled 2 price changes');

        $expected = Carbon::parse($this->nextMonday(), 'Europe/Kyiv')->utc();

        $this->assertEquals(110, $this->newPrice($this->soup));
        $this->assertEquals(164, $this->newPrice($this->salad));

        // one version of both, at the time
        /** @var MenuVersion $version */
        $version = MenuVersion::query()->sole();
        $this->assertSame(MenuVersion::STATUS_SCHEDULED, $version->status);
        $this->assertSame('Price change', $version->name);
        $this->assertSame(2, $version->countItems());
        $this->assertTrue($version->goes_live_at->equalTo($expected));

        // nothing changes until the time comes
        $this->assertEquals(100, $this->soup->fresh()->price);
    }

    /**
     * Test that variant prices can be changed by an amount, without rounding.
     *
     * @return void
     */
    public function testVariantPricesChangeByAmount()
    {
        $variant = DishVariant::factory()->withDish($this->soup)->create(['price' => 55.5]);
        $large = DishVariant::factory()->withDish($this->soup)->create(['price' => 140]);

        $this->actingAsStaff(UserRole::Admin, $this->restaurant);

        Livewire::test(VariantsRelationManager::class, ['ownerRecord' => $this->soup, 'pageClass' => EditDish::class])
            ->callTableBulkAction('schedulePriceChange', [$variant], data: [
                'goes_live_at' => $this->nextMonday(),
                'mode' => 'amount',
                'value' => -5.25,
                'round_to' => 0,
            ])
            ->assertHasNoTableBulkActionErrors();

        $this->assertEquals(50.25, $this->newPrice($variant));
        $this->assertFalse($large->scheduledChanges()->exists());
    }

    /**
     * Test that a dish changes the prices of all its sizes (archived ones stay as they are).
     *
     * @return void
     */
    public function testDishesChangeAllTheirSizes()
    {
        $large = DishVariant::factory()->withDish($this->soup)->create(['price' => 150]);
        $archived = DishVariant::factory()->withDish($this->soup)->create(['price' => 90, 'archived' => true]);

        $this->actingAsStaff(UserRole::Admin, $this->restaurant);

        Livewire::test(ListDishes::class)
            ->callTableBulkAction('schedulePriceChange', [$this->soup], data: [
                'goes_live_at' => $this->nextMonday(),
                'mode' => 'percent',
                'value' => 10,
                'round_to' => 5,
            ])
            ->assertNotified('Scheduled 2 price changes');

        /** @var DishVariant $small */
        $small = $this->soup->sizes()->where('price', 100)->sole();
        $this->assertEquals(110, $this->newPrice($small));
        $this->assertEquals(165, $this->newPrice($large));
        $this->assertFalse($archived->scheduledChanges()->exists());
    }

    /**
     * Test that a price can be set, and records already at that price are skipped.
     *
     * @return void
     */
    public function testPriceCanBeSetAndUnchangedOnesAreSkipped()
    {
        $this->actingAsStaff(UserRole::Admin, $this->restaurant);

        Livewire::test(ListDishes::class)
            ->callTableBulkAction('schedulePriceChange', [$this->soup, $this->salad], data: [
                'goes_live_at' => $this->nextMonday(),
                'mode' => 'set',
                'value' => 100,
            ])
            ->assertHasNoTableBulkActionErrors()
            ->assertNotified('Scheduled 1 price change');

        $this->assertEquals(100, $this->newPrice($this->salad));
        /** @var DishVariant $soup */
        $soup = $this->soup->sizes()->first();
        $this->assertFalse($soup->scheduledChanges()->exists());

        // a single change is a version on its own
        /** @var MenuVersion $version */
        $version = MenuVersion::query()->sole();
        $this->assertNull($version->name);
    }

    /**
     * Test that the time is taken in each restaurant's own timezone.
     *
     * @return void
     */
    public function testEachRestaurantUsesItsOwnTimezone()
    {
        $london = Restaurant::factory()->create(['timezone' => 'Europe/London']);
        $fish = Dish::factory()
            ->withMenu(DishMenu::factory()->withRestaurant($london)->create())
            ->create(['price' => 20]);

        $this->actingAsStaff();

        Livewire::test(ListDishes::class)
            ->callTableBulkAction('schedulePriceChange', [$this->soup, $fish], data: [
                'goes_live_at' => $this->nextMonday(),
                'mode' => 'percent',
                'value' => 50,
                'round_to' => 1,
            ])
            ->assertHasNoTableBulkActionErrors();

        // a version of each restaurant
        $this->assertTrue($this->changeOf($this->soup)->version->goes_live_at
            ->equalTo(Carbon::parse($this->nextMonday(), 'Europe/Kyiv')));
        $this->assertTrue($this->changeOf($fish)->version->goes_live_at
            ->equalTo(Carbon::parse($this->nextMonday(), 'Europe/London')));
    }

    /**
     * Test that nothing is scheduled when a price would be negative or the time has passed.
     *
     * @return void
     */
    public function testInvalidChangesScheduleNothing()
    {
        $this->actingAsStaff(UserRole::Admin, $this->restaurant);

        Livewire::test(ListDishes::class)
            ->callTableBulkAction('schedulePriceChange', [$this->salad, $this->soup], data: [
                'goes_live_at' => $this->nextMonday(),
                'mode' => 'amount',
                'value' => -120,
                'round_to' => 1,
            ])
            ->assertNotified('A price would be negative');

        Livewire::test(ListDishes::class)
            ->callTableBulkAction('schedulePriceChange', [$this->soup], data: [
                'goes_live_at' => Carbon::now()->subDay()->toDateTimeString(),
                'mode' => 'percent',
                'value' => 10,
                'round_to' => 1,
            ])
            ->assertNotified('The time has already passed');

        $this->assertEquals(0, MenuVersion::query()->count());
    }

    /**
     * Test that managers can't schedule price changes.
     *
     * @return void
     */
    public function testManagersCannotSchedule()
    {
        $this->actingAsStaff(UserRole::Manager, $this->restaurant);

        Livewire::test(ListDishes::class)
            ->assertTableBulkActionHidden('schedulePriceChange');
    }
}
