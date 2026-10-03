<?php

namespace Tests\Filament;

use App\Enums\UserRole;
use App\Filament\RelationManagers\ScheduledChangesRelationManager;
use App\Filament\Resources\DishResource\Pages\EditDish;
use App\Filament\Resources\DishResource\Pages\ListDishes;
use App\Filament\Resources\DishVariantResource\Pages\EditDishVariant;
use App\Filament\Resources\MenuVersionResource\Pages\ListMenuVersions;
use App\Models\BaseModel;
use App\Models\Dish;
use App\Models\DishMenu;
use App\Models\DishVariant;
use App\Models\MenuVersion;
use App\Models\MenuVersionChange;
use App\Models\Restaurant;
use App\Repositories\Editor\VersionEditorRepository;
use Carbon\Carbon;
use Livewire\Livewire;

/**
 * Class ScheduledChangesTest.
 *
 * Scheduled changes in the admin panel: versions of one change, made on the records' pages.
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
     * The dish's first size.
     *
     * @var DishVariant
     */
    protected DishVariant $size;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->restaurant = Restaurant::factory()->create(['timezone' => 'Europe/Kyiv', 'locale' => 'en']);

        $menu = DishMenu::factory()->withRestaurant($this->restaurant)->create(['title' => 'Kitchen']);

        $this->dish = Dish::factory()->withMenu($menu)->create([
            'title' => 'Borscht',
            'price' => 100,
            'flags' => ['vegetarian'],
        ]);

        /** @var DishVariant $size */
        $size = $this->dish->sizes()->sole();
        $this->size = $size;
    }

    /**
     * Test the relation manager of the record's edit page.
     *
     * @param BaseModel|null $record the dish, if not given
     *
     * @return mixed
     */
    protected function changesOf(?BaseModel $record = null): mixed
    {
        return Livewire::test(ScheduledChangesRelationManager::class, [
            'ownerRecord' => $record ?? $this->dish,
            'pageClass' => $record instanceof DishVariant ? EditDishVariant::class : EditDish::class,
        ]);
    }

    /**
     * Schedule a change of the record on its own.
     *
     * @param BaseModel $record
     * @param array $values
     *
     * @return MenuVersion
     */
    protected function scheduleFor(BaseModel $record, array $values): MenuVersion
    {
        /** @var MenuVersion $version */
        $version = app(VersionEditorRepository::class)->scheduleChange($record, $values, now()->addWeek(), null);

        return $version;
    }

    /**
     * Test that only the changed values are stored, as a version of one change at the time
     * in the restaurant's timezone.
     *
     * @return void
     */
    public function testSchedulingStoresOnlyChangedValues()
    {
        $this->actingAsStaff();

        $this->changesOf()
            ->callTableAction('schedule', data: [
                'goes_live_at' => '2030-01-07 09:00',
                'title' => 'Beet soup',
                'flag_allergens' => ['alg-celery'],
            ])
            ->assertHasNoTableActionErrors()
            ->assertNotified('The change was scheduled');

        /** @var MenuVersion $version */
        $version = MenuVersion::query()->sole();
        /** @var MenuVersionChange $change */
        $change = $version->itemChanges()->sole();

        $this->assertSame(MenuVersion::STATUS_SCHEDULED, $version->status);
        $this->assertNull($version->name);
        // 09:00 in Kyiv (UTC+2 in winter) is 07:00 UTC
        $this->assertSame('2030-01-07 07:00:00', $version->goes_live_at->toDateTimeString());
        $this->assertSame($this->restaurant->id, $version->restaurant_id);
        $this->assertSame([$this->dish->getMorphClass(), $this->dish->id], [$change->target_type, $change->target_id]);
        $this->assertSame(['en' => 'Beet soup', 'uk' => null], $change->fields['title']['new']);
        $this->assertSame(['en' => 'Borscht', 'uk' => null], $change->fields['title']['live']);
        $this->assertSame(['alg-celery', 'vegetarian'], $change->fields['flags']['new']);
        $this->assertSame(['flags', 'title'], collect($change->fields)->keys()->sort()->values()->all());

        // flags are listed with their labels (the json column orders the keys)
        $this->changesOf()
            ->assertTableColumnStateSet('changes', [
                'Flags: Celery, Vegetarian (now: Vegetarian)',
                'Title: Beet soup (now: Borscht)',
            ], $change)
            ->assertTableColumnStateSet('version.name', 'On its own', $change);
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

        $this->changesOf()
            ->mountTableAction('schedule')
            ->assertTableActionDataSet([
                'title' => 'Borscht',
                'flags' => ['vegetarian'],
                'flag_tags' => ['vegetarian'],
                'flag_hotness' => null,
                'flag_allergens' => [],
                // shown in the restaurant's timezone
                'goes_live_at' => $monday->format('Y-m-d H:i:s'),
            ]);

        $this->changesOf($this->size)
            ->mountTableAction('schedule')
            ->assertTableActionDataSet(['price' => 100, 'live' => true]);
    }

    /**
     * Test that nothing is stored when no value was changed, or the time is in the past.
     *
     * @return void
     */
    public function testInvalidSchedulesAreRejected()
    {
        $this->actingAsStaff();

        $this->changesOf()
            ->callTableAction('schedule', data: ['goes_live_at' => '2030-01-07 09:00'])
            ->assertNotified('Nothing to schedule');

        $this->changesOf($this->size)
            ->callTableAction('schedule', data: ['goes_live_at' => '2020-01-07 09:00', 'price' => 120])
            ->assertHasTableActionErrors(['goes_live_at' => 'after']);

        // the dish's only size can't be hidden
        $this->changesOf($this->size)
            ->callTableAction('schedule', data: ['goes_live_at' => '2030-01-07 09:00', 'live' => false])
            ->assertNotified(DishVariant::LAST_SIZE_MESSAGE);

        $this->assertSame(0, MenuVersion::query()->count());
    }

    /**
     * Test that a price change of a size can be scheduled and applied right away.
     *
     * @return void
     */
    public function testApplyNowAppliesTheChange()
    {
        $this->actingAsStaff();

        $this->changesOf($this->size)
            ->callTableAction('schedule', data: ['goes_live_at' => '2030-01-07 09:00', 'price' => '175'])
            ->assertHasNoTableActionErrors();

        /** @var MenuVersionChange $change */
        $change = MenuVersionChange::query()->sole();

        $this->changesOf($this->size)
            ->assertTableColumnStateSet('changes', ['Price: 175 (now: 100)'], $change)
            ->assertTableColumnStateSet('version.status', MenuVersion::STATUS_SCHEDULED, $change)
            ->callTableAction('apply', $change)
            ->assertNotified('The changes were applied');

        $this->assertEquals(175, $this->size->fresh()->price);
        // the dish shows its first size
        $this->assertEquals(175, $this->dish->fresh()->price);
        $this->assertSame(MenuVersion::STATUS_APPLIED, $change->version->fresh()->status);
    }

    /**
     * Test that a version, which can't be applied, is marked as failed, and nothing of it is applied.
     *
     * @return void
     */
    public function testApplyNowMarksFailures()
    {
        $this->actingAsStaff();

        // planned when the dish had another size, which is gone now
        $version = MenuVersion::factory()->withRestaurant($this->restaurant)->scheduled()->create();
        $change = MenuVersionChange::factory()->inVersion($version)
            ->changing($this->size, ['is_hidden' => true, 'price' => 90])
            ->create();

        $this->changesOf($this->size)
            ->callTableAction('apply', $change)
            ->assertNotified('The changes couldn\'t be applied');

        $this->assertEquals(100, $this->size->fresh()->price);
        $this->assertFalse($this->size->fresh()->is_hidden);
        $this->assertSame(MenuVersion::STATUS_FAILED, $version->fresh()->status);
        $this->assertSame(DishVariant::LAST_SIZE_MESSAGE, $version->fresh()->failure_reason);
    }

    /**
     * Test that a change can be cancelled: its version goes with it, when it was its only change.
     *
     * @return void
     */
    public function testCancelRemovesTheChange()
    {
        $this->actingAsStaff();

        $version = $this->scheduleFor($this->dish, ['title' => 'Beet soup']);
        $change = $version->itemChanges()->sole();

        $this->changesOf()
            ->callTableAction('remove', $change)
            ->assertNotified('The scheduled change was cancelled');

        $this->assertModelMissing($change);
        $this->assertModelMissing($version);
    }

    /**
     * Test that managers can see scheduled changes, but not schedule, apply or cancel them.
     *
     * @return void
     */
    public function testManagersCanOnlyView()
    {
        $change = $this->scheduleFor($this->dish, ['title' => 'Beet soup'])->itemChanges()->sole();

        $this->actingAsStaff(UserRole::Manager, $this->restaurant);

        $this->changesOf()
            ->assertCanSeeTableRecords([$change])
            ->assertTableActionHidden('schedule')
            ->assertTableActionHidden('apply', $change)
            ->assertTableActionHidden('remove', $change);
    }

    /**
     * Test that hiding a size can be scheduled, when the dish has another one.
     *
     * @return void
     */
    public function testHidingASizeCanBeScheduled()
    {
        $this->actingAsStaff();

        $variant = DishVariant::factory()->withDish($this->dish)->create(['price' => 150]);

        $this->changesOf($variant)
            ->callTableAction('schedule', data: ['goes_live_at' => '2030-01-07 09:00', 'live' => false])
            ->assertHasNoTableActionErrors();

        /** @var MenuVersionChange $change */
        $change = MenuVersionChange::query()->sole();

        // the json column orders the keys
        $this->assertEquals(['is_hidden' => ['live' => false, 'new' => true]], $change->fields);

        // described as "Live", like the toggle
        $this->changesOf($variant)
            ->assertTableColumnStateSet('changes', ['Live: No (now: Yes)'], $change);
    }

    /**
     * Test that the global page lists the versions of the user's restaurant (not other
     * restaurants' ones) and filters them by status.
     *
     * @return void
     */
    public function testGlobalPageIsScopedAndFilterable()
    {
        $scheduled = $this->scheduleFor($this->dish, ['title' => 'Beet soup']);
        $failed = MenuVersion::factory()->withRestaurant($this->restaurant)->create([
            'name' => 'Winter menu',
            'status' => MenuVersion::STATUS_FAILED,
            'failure_reason' => 'A changed dish doesn\'t exist anymore.',
        ]);

        $otherMenu = DishMenu::factory()->withRestaurant(Restaurant::factory()->create())->create();
        $otherDish = Dish::factory()->withMenu($otherMenu)->create();
        $other = $this->scheduleFor($otherDish, ['title' => 'Other']);

        $this->actingAsStaff(UserRole::Admin, $this->restaurant);

        Livewire::test(ListMenuVersions::class)
            ->assertCanSeeTableRecords([$scheduled, $failed])
            ->assertCanNotSeeTableRecords([$other])
            ->assertTableColumnStateSet('name', ['Dish · Borscht', 'Title: Beet soup (now: Borscht)'], $scheduled)
            ->assertTableColumnStateSet('name', ['Winter menu', '0 changes in 0 items'], $failed)
            ->filterTable('status', MenuVersion::STATUS_FAILED)
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
        $this->scheduleFor($this->dish, ['title' => 'Beet soup']);

        $applied = $this->scheduleFor($other, ['title' => 'Other']);
        app(VersionEditorRepository::class)->apply($applied);

        $this->actingAsStaff();

        // keys, so the records are loaded through the table's query (with the count)
        Livewire::test(ListDishes::class)
            ->assertTableColumnStateSet('scheduled_changes_count', 1, $this->dish->getKey())
            ->assertTableColumnStateSet('scheduled_changes_count', 0, $other->getKey());
    }
}
