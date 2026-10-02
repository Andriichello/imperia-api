<?php

namespace Tests\Filament;

use App\Enums\UserRole;
use App\Filament\Resources\RestaurantResource\Pages\EditRestaurant;
use App\Filament\Resources\RestaurantResource\RelationManagers\SchedulesRelationManager;
use App\Models\Restaurant;
use App\Models\Schedule;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\EditAction;
use Illuminate\Support\Collection;
use Livewire\Livewire;

/**
 * Class SchedulesRelationManagerTest.
 */
class SchedulesRelationManagerTest extends FilamentTestCase
{
    /**
     * @var Restaurant
     */
    protected Restaurant $restaurant;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->restaurant = Restaurant::factory()->create(['timezone' => 'Europe/Kyiv']);
    }

    /**
     * Test the relation manager of the restaurant's edit page.
     *
     * @return mixed
     */
    protected function hours(): mixed
    {
        return Livewire::test(SchedulesRelationManager::class, [
            'ownerRecord' => $this->restaurant,
            'pageClass' => EditRestaurant::class,
        ]);
    }

    /**
     * Create opening hours of the restaurant.
     *
     * @param string $weekday
     * @param array $attributes
     *
     * @return Schedule
     */
    protected function schedule(string $weekday, array $attributes = []): Schedule
    {
        return Schedule::factory()->withRestaurant($this->restaurant)->withWeekday($weekday)->create(array_merge([
            'beg_hour' => 10,
            'beg_minute' => 0,
            'end_hour' => 22,
            'end_minute' => 0,
            'archived' => false,
        ], $attributes));
    }

    /**
     * Test that the hours are listed from Monday to Sunday, with times.
     *
     * @return void
     */
    public function testHoursAreListedByWeekday()
    {
        $sunday = $this->schedule('sunday', ['end_hour' => 2, 'end_minute' => 30]);
        $monday = $this->schedule('monday', ['beg_hour' => 9, 'beg_minute' => 5]);

        $other = Schedule::factory()->withRestaurant(Restaurant::factory()->create())
            ->withWeekday('monday')->create();

        $this->actingAsStaff();

        $this->hours()
            ->assertCanSeeTableRecords([$monday, $sunday], inOrder: true)
            ->assertCanNotSeeTableRecords([$other])
            ->assertTableColumnFormattedStateSet('weekday', 'Monday', $monday)
            ->assertTableColumnStateSet('opens', '09:05', $monday)
            ->assertTableColumnStateSet('closes', '22:00', $monday)
            ->assertTableColumnStateSet('closes', '02:30 (next day)', $sunday);
    }

    /**
     * Test that a day can be added and edited with times.
     *
     * @return void
     */
    public function testDayCanBeAddedAndEdited()
    {
        $this->actingAsStaff(UserRole::Admin, $this->restaurant);

        $this->hours()
            ->callTableAction(CreateAction::class, data: [
                'weekday' => 'friday',
                'opens_at' => '11:30',
                'closes_at' => '23:45',
            ])
            ->assertHasNoTableActionErrors();

        /** @var Schedule $friday */
        $friday = Schedule::query()->sole();
        $this->assertSame($this->restaurant->id, $friday->restaurant_id);
        $this->assertSame('friday', $friday->weekday);
        $this->assertSame([11, 30, 23, 45], [
            $friday->beg_hour, $friday->beg_minute, $friday->end_hour, $friday->end_minute,
        ]);

        $this->hours()
            ->mountTableAction(EditAction::class, $friday)
            ->assertTableActionDataSet(['weekday' => 'friday', 'opens_at' => '11:30', 'closes_at' => '23:45'])
            ->setTableActionData(['opens_at' => '12:00', 'closes_at' => '01:00', 'archived' => true])
            ->callMountedTableAction()
            ->assertHasNoTableActionErrors();

        $friday->refresh();
        $this->assertSame([12, 0, 1, 0], [
            $friday->beg_hour, $friday->beg_minute, $friday->end_hour, $friday->end_minute,
        ]);
        $this->assertTrue((bool) $friday->archived);
        $this->assertTrue($friday->is_cross_date);
    }

    /**
     * Test that a day can't be added twice, while other restaurants can have it.
     *
     * @return void
     */
    public function testDayCannotBeAddedTwice()
    {
        $this->schedule('friday');
        Schedule::factory()->withRestaurant(Restaurant::factory()->create())->withWeekday('saturday')->create();

        $this->actingAsStaff();

        $this->hours()
            ->callTableAction(CreateAction::class, data: [
                'weekday' => 'friday',
                'opens_at' => '11:00',
                'closes_at' => '20:00',
            ])
            ->assertHasTableActionErrors(['weekday' => 'unique'])
            ->callTableAction(CreateAction::class, data: [
                'weekday' => 'saturday',
                'opens_at' => '11:00',
                'closes_at' => '20:00',
            ])
            ->assertHasNoTableActionErrors();

        $this->assertSame(2, $this->restaurant->schedules()->count());
    }

    /**
     * Test that weekly hours replace the selected days and add the missing ones.
     *
     * @return void
     */
    public function testWeeklyHoursFillTheSelectedDays()
    {
        $monday = $this->schedule('monday', ['archived' => true]);
        $sunday = $this->schedule('sunday', ['beg_hour' => 12]);

        $this->actingAsStaff(UserRole::Admin, $this->restaurant);

        $this->hours()
            ->mountTableAction('weeklyHours')
            ->assertTableActionDataSet([
                'weekdays' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'],
            ])
            // the test helper merges arrays, so the default selection is cleared first
            ->setTableActionData(['weekdays' => []])
            ->setTableActionData([
                'weekdays' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
                'opens_at' => '08:00',
                'closes_at' => '18:30',
            ])
            ->callMountedTableAction()
            ->assertHasNoTableActionErrors()
            ->assertNotified('The opening hours were saved');

        /** @var Collection<string, Schedule> $schedules */
        $schedules = $this->restaurant->schedules()->get()->keyBy('weekday');

        $this->assertCount(6, $schedules);
        $this->assertSame($monday->id, $schedules['monday']->id);
        $this->assertFalse((bool) $schedules['monday']->archived);

        foreach (['monday', 'tuesday', 'wednesday', 'thursday', 'friday'] as $weekday) {
            $this->assertSame([8, 0, 18, 30], [
                $schedules[$weekday]->beg_hour, $schedules[$weekday]->beg_minute,
                $schedules[$weekday]->end_hour, $schedules[$weekday]->end_minute,
            ], $weekday);
        }

        // not selected
        $this->assertSame(12, $schedules['sunday']->beg_hour);
        $this->assertSame($sunday->id, $schedules['sunday']->id);
    }

    /**
     * Test that managers can only see the hours.
     *
     * @return void
     */
    public function testManagersCanOnlyView()
    {
        $monday = $this->schedule('monday');

        $this->actingAsStaff(UserRole::Manager, $this->restaurant);

        $this->hours()
            ->assertCanSeeTableRecords([$monday])
            ->assertTableActionHidden('weeklyHours')
            ->assertTableActionHidden(CreateAction::class)
            ->assertTableActionHidden(EditAction::class, $monday);
    }
}
