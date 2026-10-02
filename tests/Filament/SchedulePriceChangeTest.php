<?php

namespace Tests\Filament;

use App\Enums\UserRole;
use App\Filament\Resources\DishResource\Pages\EditDish;
use App\Filament\Resources\DishResource\Pages\ListDishes;
use App\Filament\Resources\DishResource\RelationManagers\VariantsRelationManager;
use App\Models\Dish;
use App\Models\DishMenu;
use App\Models\DishVariant;
use App\Models\Morphs\Alteration;
use App\Models\Restaurant;
use Carbon\Carbon;
use Livewire\Livewire;

/**
 * Class SchedulePriceChangeTest.
 *
 * The bulk action, which schedules price changes of dishes and variants.
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
     * The only scheduled change of the record.
     *
     * @param Dish|DishVariant $record
     *
     * @return Alteration
     */
    protected function changeOf(Dish|DishVariant $record): Alteration
    {
        /** @var Alteration $alteration */
        $alteration = Alteration::query()
            ->where('alterable_type', $record->getMorphClass())
            ->where('alterable_id', $record->getKey())
            ->sole();

        return $alteration;
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
                'perform_at' => $this->nextMonday(),
                'mode' => 'percent',
                'value' => 10,
                'round_to' => 1,
            ])
            ->assertHasNoTableBulkActionErrors()
            ->assertNotified('Scheduled 2 price changes');

        $expected = Carbon::parse($this->nextMonday(), 'Europe/Kyiv')->utc();

        $this->assertEquals(['price' => 110], $this->changeOf($this->soup)->getJson('metadata'));
        $this->assertEquals(['price' => 164], $this->changeOf($this->salad)->getJson('metadata'));
        $this->assertTrue($this->changeOf($this->soup)->perform_at->equalTo($expected));

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

        $this->actingAsStaff(UserRole::Admin, $this->restaurant);

        Livewire::test(VariantsRelationManager::class, ['ownerRecord' => $this->soup, 'pageClass' => EditDish::class])
            ->callTableBulkAction('schedulePriceChange', [$variant], data: [
                'perform_at' => $this->nextMonday(),
                'mode' => 'amount',
                'value' => -5.25,
                'round_to' => 0,
            ])
            ->assertHasNoTableBulkActionErrors();

        $this->assertEquals(['price' => 50.25], $this->changeOf($variant)->getJson('metadata'));
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
                'perform_at' => $this->nextMonday(),
                'mode' => 'set',
                'value' => 100,
            ])
            ->assertHasNoTableBulkActionErrors()
            ->assertNotified('Scheduled 1 price change');

        $this->assertEquals(['price' => 100], $this->changeOf($this->salad)->getJson('metadata'));
        $this->assertFalse($this->soup->alterations()->exists());
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
                'perform_at' => $this->nextMonday(),
                'mode' => 'percent',
                'value' => 50,
                'round_to' => 1,
            ])
            ->assertHasNoTableBulkActionErrors();

        $this->assertTrue($this->changeOf($this->soup)->perform_at
            ->equalTo(Carbon::parse($this->nextMonday(), 'Europe/Kyiv')));
        $this->assertTrue($this->changeOf($fish)->perform_at
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
                'perform_at' => $this->nextMonday(),
                'mode' => 'amount',
                'value' => -120,
                'round_to' => 1,
            ])
            ->assertNotified('A price would be negative');

        Livewire::test(ListDishes::class)
            ->callTableBulkAction('schedulePriceChange', [$this->soup], data: [
                'perform_at' => Carbon::now()->subDay()->toDateTimeString(),
                'mode' => 'percent',
                'value' => 10,
                'round_to' => 1,
            ])
            ->assertNotified('The time has already passed');

        $this->assertEquals(0, Alteration::query()->count());
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
