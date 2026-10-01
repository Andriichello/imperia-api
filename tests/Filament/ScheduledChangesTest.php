<?php

namespace Tests\Filament;

use App\Enums\UserRole;
use App\Filament\RelationManagers\AlterationsRelationManager;
use App\Filament\Resources\AlterationResource\Pages\ListAlterations;
use App\Filament\Resources\DishResource\Pages\EditDish;
use App\Filament\Resources\DishResource\Pages\ListDishes;
use App\Filament\Resources\DishVariantResource\Pages\EditDishVariant;
use App\Models\Dish;
use App\Models\DishMenu;
use App\Models\DishVariant;
use App\Models\Morphs\Alteration;
use App\Models\Restaurant;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Filament\Tables\Actions\DeleteAction;
use Livewire\Livewire;
use RuntimeException;

/**
 * Class ScheduledChangesTest.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ScheduledChangesTest extends FilamentTestCase
{
    /**
     * @var Restaurant
     */
    protected Restaurant $restaurant;

    /**
     * @var Dish
     */
    protected Dish $dish;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->restaurant = Restaurant::factory()->create(['timezone' => 'Europe/Kyiv']);

        $menu = DishMenu::factory()->withRestaurant($this->restaurant)->create(['title' => 'Kitchen']);

        $this->dish = Dish::factory()->withMenu($menu)->create([
            'title' => 'Borscht',
            'price' => 100,
            'flags' => ['vegetarian'],
        ]);
    }

    /**
     * Test the relation manager of the dish's edit page.
     *
     * @return mixed
     */
    protected function dishChanges(): mixed
    {
        return Livewire::test(AlterationsRelationManager::class, [
            'ownerRecord' => $this->dish,
            'pageClass' => EditDish::class,
        ]);
    }

    /**
     * Create a scheduled change of the dish.
     *
     * @param array $values
     * @param CarbonInterface|null $performAt
     *
     * @return Alteration
     */
    protected function scheduleForDish(array $values, ?CarbonInterface $performAt = null): Alteration
    {
        return Alteration::factory()
            ->withModel($this->dish)
            ->withValues($values)
            ->performAt($performAt ?? now()->addWeek())
            ->create();
    }

    /**
     * Test that only the changed values are stored, with the time converted
     * from the restaurant's timezone and the restaurant filled in.
     *
     * @return void
     */
    public function testSchedulingStoresOnlyChangedValues()
    {
        $this->actingAsStaff();

        $this->dishChanges()
            ->callTableAction('schedule', data: [
                'perform_at' => '2030-01-07 09:00',
                'price' => '120',
                'flag_allergens' => ['alg-celery'],
            ])
            ->assertHasNoTableActionErrors()
            ->assertNotified('The change was scheduled');

        /** @var Alteration $alteration */
        $alteration = Alteration::query()->sole();

        $this->assertEquals(
            ['price' => 120, 'flags' => ['vegetarian', 'alg-celery']],
            $alteration->getJson('metadata')
        );
        // 09:00 in Kyiv (UTC+2 in winter) is 07:00 UTC
        $this->assertSame('2030-01-07 07:00:00', $alteration->perform_at->toDateTimeString());
        $this->assertSame($this->restaurant->id, $alteration->restaurant_id);
        $this->assertSame($this->dish->getMorphClass(), $alteration->alterable_type);

        // flags are listed with their labels (the json column orders the keys)
        $this->dishChanges()
            ->assertTableColumnStateSet('changes', [
                'Flags: Vegetarian, Celery (now: Vegetarian)',
                'Price: 120 (now: 100)',
            ], $alteration);
    }

    /**
     * Test that the form starts with the current values and next Monday.
     *
     * @return void
     */
    public function testFormIsFilledWithCurrentValues()
    {
        $this->actingAsStaff();

        $monday = Carbon::now('Europe/Kyiv')->next(Carbon::MONDAY);

        $this->dishChanges()
            ->mountTableAction('schedule')
            ->assertTableActionDataSet([
                'title' => 'Borscht',
                'price' => 100,
                'flags' => ['vegetarian'],
                'flag_tags' => ['vegetarian'],
                'flag_hotness' => null,
                'flag_allergens' => [],
                // shown in the restaurant's timezone
                'perform_at' => $monday->format('Y-m-d H:i'),
            ]);
    }

    /**
     * Test that nothing is stored when no value was changed, or the time is in the past.
     *
     * @return void
     */
    public function testInvalidSchedulesAreRejected()
    {
        $this->actingAsStaff();

        $this->dishChanges()
            ->callTableAction('schedule', data: ['perform_at' => '2030-01-07 09:00'])
            ->assertNotified('Nothing to schedule');

        $this->dishChanges()
            ->callTableAction('schedule', data: ['perform_at' => '2020-01-07 09:00', 'price' => 120])
            ->assertHasTableActionErrors(['perform_at' => 'after']);

        $this->assertSame(0, Alteration::query()->count());
    }

    /**
     * Test that a change can be applied right away.
     *
     * @return void
     */
    public function testRunNowAppliesTheChange()
    {
        $this->actingAsStaff();

        $alteration = $this->scheduleForDish(['price' => 175]);

        $this->dishChanges()
            ->assertTableColumnStateSet('changes', ['Price: 175 (now: 100)'], $alteration)
            ->assertTableColumnStateSet('status', Alteration::STATUS_SCHEDULED, $alteration)
            ->callTableAction('run', $alteration)
            ->assertNotified('The change was applied');

        $this->assertEquals(175, $this->dish->fresh()->price);
        $this->assertSame(Alteration::STATUS_DONE, $alteration->fresh()->getStatus());
    }

    /**
     * Test that a change, which can't be applied, is marked as failed.
     *
     * @return void
     */
    public function testRunNowMarksFailures()
    {
        $this->actingAsStaff();

        // the price column is unsigned, so the database rejects it
        $alteration = $this->scheduleForDish(['price' => -5]);

        $this->dishChanges()
            ->callTableAction('run', $alteration)
            ->assertNotified('The change failed');

        $this->assertEquals(100, $this->dish->fresh()->price);
        $this->assertSame(Alteration::STATUS_FAILED, $alteration->fresh()->getStatus());
    }

    /**
     * Test that a scheduled change can be cancelled.
     *
     * @return void
     */
    public function testCancelDeletesTheChange()
    {
        $this->actingAsStaff();

        $alteration = $this->scheduleForDish(['price' => 175]);

        $this->dishChanges()
            ->callTableAction(DeleteAction::class, $alteration);

        $this->assertModelMissing($alteration);
    }

    /**
     * Test that managers can see scheduled changes, but not create, run or cancel them.
     *
     * @return void
     */
    public function testManagersCanOnlyView()
    {
        $alteration = $this->scheduleForDish(['price' => 175]);

        $this->actingAsStaff(UserRole::Manager, $this->restaurant);

        $this->dishChanges()
            ->assertCanSeeTableRecords([$alteration])
            ->assertTableActionHidden('schedule')
            ->assertTableActionHidden('run', $alteration)
            ->assertTableActionHidden(DeleteAction::class, $alteration);
    }

    /**
     * Test that archiving a variant can be scheduled.
     *
     * @return void
     */
    public function testVariantArchivingCanBeScheduled()
    {
        $this->actingAsStaff();

        $variant = DishVariant::factory()->withDish($this->dish)->create(['price' => 150]);

        Livewire::test(AlterationsRelationManager::class, [
            'ownerRecord' => $variant,
            'pageClass' => EditDishVariant::class,
        ])
            ->callTableAction('schedule', data: ['perform_at' => '2030-01-07 09:00', 'archived' => true])
            ->assertHasNoTableActionErrors();

        /** @var Alteration $alteration */
        $alteration = Alteration::query()->sole();

        $this->assertSame(['archived' => true], $alteration->getJson('metadata'));
        $this->assertSame($this->restaurant->id, $alteration->restaurant_id);
    }

    /**
     * Test that the global page lists the dish changes of the user's restaurant
     * (not other restaurants, not the old menu), and filters them by status.
     *
     * @return void
     */
    public function testGlobalPageIsScopedAndFilterable()
    {
        $scheduled = $this->scheduleForDish(['price' => 175]);
        $failed = $this->scheduleForDish(['price' => 180], now()->subDay());
        $failed->markAsFailed(new RuntimeException('Failed'));

        $otherMenu = DishMenu::factory()->withRestaurant(Restaurant::factory()->create())->create();
        $otherDish = Dish::factory()->withMenu($otherMenu)->create();
        $other = Alteration::factory()->withModel($otherDish)->withValues(['price' => 1])->create();

        $oldMenu = Alteration::factory()
            ->withValues(['price' => 1])
            ->create(['alterable_id' => 1, 'alterable_type' => 'products', 'restaurant_id' => $this->restaurant->id]);

        $this->actingAsStaff(UserRole::Admin, $this->restaurant);

        Livewire::test(ListAlterations::class)
            ->assertCanSeeTableRecords([$scheduled, $failed])
            ->assertCanNotSeeTableRecords([$other, $oldMenu])
            ->assertTableColumnStateSet('subject', 'Dish · Borscht', $scheduled)
            ->filterTable('status', Alteration::STATUS_FAILED)
            ->assertCanSeeTableRecords([$failed])
            ->assertCanNotSeeTableRecords([$scheduled]);
    }

    /**
     * Test that the dishes table shows which dishes have scheduled changes.
     *
     * @return void
     */
    public function testDishesTableShowsScheduledChanges()
    {
        $other = Dish::factory()->withMenu($this->dish->menu)->create();
        $this->scheduleForDish(['price' => 175]);

        $done = Alteration::factory()->withModel($other)->withValues(['price' => 1])->create();
        $done->perform();

        $this->actingAsStaff();

        // keys, so the records are loaded through the table's query (with the count)
        Livewire::test(ListDishes::class)
            ->assertTableColumnStateSet('scheduled_changes_count', 1, $this->dish->getKey())
            ->assertTableColumnStateSet('scheduled_changes_count', 0, $other->getKey());
    }
}
